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
use App\Repositories\Eloquent\RemarkRepositoryInterface;
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
                                RemarkRepositoryInterface $remarkRepository,
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
    public function getUserOrders(Request $request)
    {

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
        $order_sn = generate_order_sn('TAKE-');
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
            'name' => '发布代拿',
            'user_id' => $user->id,
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
            'body' => "发布代拿",
            'detail' => "发布代拿",
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

    /**
     * 发单人结算任务
     */
    public function completeOrder(Request $request)
    {
        //检验请求参数
        $rule = [
            'id' => 'required|integer',
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

    public function cancelOrder(Request $request)
    {
        $rule = [
            'id' => 'required|integer',
        ];
        validateParameter($rule);

        $user = User::tokenAuth();

        $take_order = $this->takeOrderRepository->find($request->id);

        if($take_order->user_id != $user->id){
            throw new PermissionDeniedException('没有取消该任务的权限');
        }
        $this->takeOrderRepository->userCancelOrder($take_order);

    }

    public function agreeCancelOrder(Request $request)
    {
        $rule = [
            'id' => 'required|integer',
        ];
        validateParameter($rule);

        $user = User::tokenAuth();

        $take_order = $this->takeOrderRepository->find($request->id);

        if($take_order->user_id != $user->id){
            throw new PermissionDeniedException();
        }

        $this->takeOrderRepository->agreeCancelOrder($take_order);
    }

}