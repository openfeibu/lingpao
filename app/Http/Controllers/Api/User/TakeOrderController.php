<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\PermissionDeniedException;
use App\Http\Controllers\Api\BaseController;
use App\Exceptions\NotFoundPayPasswordException;
use App\Exceptions\OutputServerMessageException;
use App\Models\User;
use App\Models\TakeOrder;
use App\Repositories\Eloquent\UserCouponRepositoryInterface;
use App\Repositories\Eloquent\UserRepositoryInterface;
use App\Repositories\Eloquent\TakeOrderRepositoryInterface;
use App\Repositories\Eloquent\TakeOrderExpressRepositoryInterface;
use App\Repositories\Eloquent\TaskOrderRepositoryInterface;
use App\Services\PayService;
use Log,DB;
use Illuminate\Http\Request;

class TakeOrderController extends BaseController
{
    public $takeOrderRepository;
    public $takeOrderExpressRepository;
    public $userCouponRepository;
    public $userRepository;
    public $taskOrderRepository;
    public $payService;

    public function __construct(TakeOrderRepositoryInterface $takeOrderRepository,
                                TakeOrderExpressRepositoryInterface $takeOrderExpressRepository,
                                UserCouponRepositoryInterface $userCouponRepository,
                                UserRepositoryInterface $userRepository,
                                TaskOrderRepositoryInterface $taskOrderRepository,
                                PayService $payService)
    {
        parent::__construct();
        $this->middleware('auth.api',['except' => ['extractExpressInfo','getOrders']]);
        $this->takeOrderRepository = $takeOrderRepository;
        $this->takeOrderExpressRepository = $takeOrderExpressRepository;
        $this->userCouponRepository = $userCouponRepository;
        $this->userRepository = $userRepository;
        $this->taskOrderRepository = $taskOrderRepository;
        $this->payService = $payService;
    }
    public function createOrder(Request $request)
    {
        $user = User::tokenAuth();
        $order_data = $request->all();
        $rule = [
            'urgent' => 'sometimes|in:0,1',
            'urgent_price' => 'sometimes|numeric|min:0',
            'tip' => 'sometimes|numeric|min:0',
            'payment' => "required|in:wechat,balance",
            "postscript" => 'sometimes|required|string'
        ];
        validateCustomParameter($order_data,$rule);

        $expresses = $request->get('expresses',[]);
        $express_count = count($expresses);

        if(!$express_count){
            throw new OutputServerMessageException('请先完善订单');
        }

        $express_rule = [
            'take_place' => 'required',
            'consignee' => 'required',
            'mobile' => 'required|regex:'.config('regex.phone'),
            'address' => 'required',
            'description' => 'sometimes',
            'take_code' => 'sometimes',
            'express_company' => 'required',
            'express_arrive_date' => 'required',
        ];

        foreach ($expresses as $key => $express)
        {
            validateCustomParameter($express,$express_rule);
        }

        $tip = !empty($order_data['tip']) ? $order_data['tip'] : 0;

        $urgent = !empty($order_data['urgent']) ? $order_data['urgent'] : 0;
        $express_price = setting('take_order_min_price');
        $urgent_price = 0;
        if($urgent)
        {
            $express_price = setting('urgent_min_price');
            $urgent_price = setting('urgent_min_price');
        }
        $total_price = $express_price * $express_count + $tip;

        //骑手所得款项
        $deliverer_price = $express_price * $express_count + $tip;
        //check_urgent_price($urgent_price);

        $user_coupon_id = !empty($request->coupon_id) ? intval($request->coupon_id): 0;
        if($user_coupon_id)
        {

            $coupon = $this->userCouponRepository->getAvailableCoupon(['user_id' => $user->id,'id' => $user_coupon_id],$total_price);
            if(!$coupon)
            {
                throw new \App\Exceptions\OutputServerMessageException('优惠券不存在或不可用');
            }
            $total_price =  $total_price - $coupon->price;
        }

        if($order_data['payment'] == 'balance')
        {
            if(!$user->is_pay_password)
            {
                throw new NotFoundPayPasswordException();
            }
            if (!password_verify($request->pay_password, $user->pay_password)) {
                throw new \App\Exceptions\OutputServerMessageException('支付密码错误');
            }
            if($total_price > $user->balance){
                throw new \App\Exceptions\OutputServerMessageException('余额不足,请选择其他支付方式');
            }
        }
        $order_sn = generate_order_sn('TAKEORDER-');
        $order_data = [
            'order_sn' => $order_sn,
            'user_id' => $user->id,
            'urgent' => $urgent,
            'urgent_price' => $urgent_price,
            'tip' => !empty($order_data['tip']) ? $order_data['tip'] : 0,
            'payment' => $order_data['payment'],
            'total_price' => $total_price,
            'express_price' => $express_price,
            'original_price' => $express_price * $express_count ,
            'express_count' => $express_count,
            'coupon_id' => $user_coupon_id,
            'coupon_name' => isset($coupon) && !empty($coupon) ? '满'.$coupon->min_price.'减'.$coupon->price : '',
            'coupon_price' => isset($coupon) && !empty($coupon) ? $coupon->price : 0,
            'deliverer_price' => $deliverer_price,
            'order_status' => 'unpaid',
            'postscript' => !empty($order_data['postscript']) ? $order_data['postscript'] : '',
        ];

        $order = $this->takeOrderRepository->create($order_data);
        $task_order = $this->taskOrderRepository->create([
            'name' => '代拿',
            'objective_id' => $order->id,
            'objective_model' => 'TakeOrder',
            'type' => 'take_order',
        ]);
        foreach ($expresses as $key => $express)
        {
            $express['take_order_id'] = $order->id;
            $this->takeOrderExpressRepository->create($express);
        }
        $data = [
            'task_order_id' => $task_order->id,
            'take_order_id' => $order->id,
            'order_sn' => $order_sn,
            'body' => "代拿",
            'detail' => "代拿",
            'total_price' => $total_price,
            'trade_type' => 'CREATE_TAKE_ORDER',
            'payment' => $request->payment,
            'pay_from' => 'TakeOrder',
            'user_coupon_id' => $user_coupon_id,
        ];
        $data = $this->payService->payHandle($data);

        return $this->response->success()->data($data)->json();
    }
    public function extractExpressInfo(Request $request)
    {
        $rule = [
            'description' => 'required',
        ];
        validateParameter($rule);
        $description = $request->description;
        $data = [
            'take_code' => '',
            'express_company' => '',
        ];

        preg_match('/[\da-zA-Z]{1,}-[\da-zA-Z]{1,}-[\da-zA-Z]{1,}|[\da-zA-Z]{1,}-[\da-zA-Z]{1,}/',$description,$code_result);
        if($code_result)
        {
            $data['take_code'] = $code_result[0];
        }else{
            preg_match('/(货号|取件码|取货码|凭)(?P<code>[0-9a-zA-Z])/',$description,$code_result);
            $data['take_code'] = $code_result ? $code_result['code'] : '';
        }

        $express_companies = config('regex.express_company');

        foreach ($express_companies as $key => $express_company)
        {
            if(strpos($description,$express_company) !== false)
            {
                $data['express_company'] = $express_company;
                break;
            }
        }

        return $this->response->success()->data($data)->json();

    }
    public function getOrders(Request $request)
    {
        $take_orders = $this->takeOrderRepository->getOrders();
        $data = $take_orders->toArray()['data'];
        return $this->response->success()->count($take_orders->total())->data($data)->json();
    }
    public function getOrder(Request $request,$id)
    {
        $take_order = $this->takeOrderRepository->getOrderDetail($id);

        return $this->response->success()->data($take_order)->json();
    }
/*
    public function cancelOrder(Request $request)
    {
//检验请求参数
        $rule = [
            'id' => 'required|integer',
        ];
        validateParameter($rule);

        $user = User::tokenAuth();

        //检验任务是否已被接
        $take_order = $this->takeOrderRepository->find($request->order_id);

        if ($take_order->courier_id == $this->user->uid) {

            if ($order->status != 'accepted') {
                throw new \App\Exceptions\Custom\OutputServerMessageException('当前任务状态不允许取消');
            }
            $param = [
                'order_id' => $request->order_id,
                'only_in_status' => ['accepted'],
                'status' => 'new',
                'courier_cancel' => true
            ];
            //重新生成取款码
            if($order->type == 'business')
            {
                $this->orderInfoService->uploadPickCode(['order_id' => $order->order_id]);
            }
            else if($order->type == 'canteen')
            {
                $this->orderInfoService->updateOrderInfoById($order->order_id,['shipping_status' => 0]);
            }

            $this->orderService->updateOrderStatus($param);

            throw new \App\Exceptions\Custom\RequestSuccessException();
        }

        if($order->owner_id == $this->user->uid){
            if ($order->status != 'new') {
                throw new \App\Exceptions\Custom\OutputServerMessageException('当前任务状态不允许取消');
            }

            $param = [
                'order_id' => $request->order_id,
                'only_in_status' => ['new'],
            ];

            if($order->type == 'business')
            {
                //商家任务取消
                $param['status'] = 'cancelled';
                $this->orderService->delete(['oid' => $request->order_id ]);
                //更新订单状态
                $this->orderInfoService->updateOrderInfoById($order->order_id,['shipping_status' => 0]);
                return [
                    'code' => 200,
                    'detail' => '取消任务成功，请重新发货',
                ];
            }
            else
            {
                //个人任务取消
                if($order->pay_id == 3){
                    $walletData = array(
                        'uid' => $this->user->uid,
                        'wallet' => $this->user->wallet + $order->fee,
                        'fee'	=> $order->fee,
                        'service_fee' => 0,
                        'out_trade_no' => $order->order_sn,
                        'pay_id' => $order->pay_id,
                        'wallet_type' => 1,
                        'trade_type' => 'CancelTask',
                        'description' => '取消任务',
                    );
                    $this->walletService->store($walletData);
                    $tradeData = array(
                        'wallet_type' => 1,
                        'trade_type' => 'CancelTask',
                        'description' => '取消任务',
                        'trade_status' => 'refunded',
                    );
                    $param['status'] = 'cancelled';
                    $this->walletService->updateWallet($order->owner_id,$this->user->wallet + $order->fee);
                    $this->tradeAccountService->updateTradeAccount($order->order_sn,$tradeData);
                    //取消任务
                    $this->orderService->updateOrderStatus($param);
                    return [
                        'code' => 200,
                        'detail' => '取消任务成功，任务费用已返回您的钱包，请查收',
                    ];
                }
                else{
                    $tradeData = array(
                        'wallet_type' => 1,
                        'trade_type' => 'CancelTask',
                        'trade_status' => 'refunding',
                        'description' => '取消任务',
                    );
                    $param['status'] = 'cancelling';
                    $this->tradeAccountService->updateTradeAccount($order->order_sn,$tradeData);
                    //取消任务
                    $this->orderService->updateOrderStatus($param);
                    return [
                        'code' => 200,
                        'detail' => '取消任务成功，等待管理员审核',
                    ];
                }
            }
        }
        throw new \App\Exceptions\OutputServerMessageException('没有取消该任务的权限');
    }
*/
    /**
     * 发单人结算任务
     */
    public function completeOrder(Request $request)
    {
        //检验请求参数
        $rule = [
            'id' => 'required|integer',
            'pay_password' => 'required|string',
        ];
        validateParameter($rule);

        $user = User::tokenAuth();

        $take_order = $this->takeOrderRepository->find($request->id);

        if($take_order->user_id != $user->id)
        {
            throw new PermissionDeniedException();
        }

        $this->takeOrderRepository->completeOrder($take_order);

        throw new \App\Exceptions\RequestSuccessException("确认成功！");
    }
}