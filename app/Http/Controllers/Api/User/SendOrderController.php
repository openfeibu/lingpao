<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\PermissionDeniedException;
use App\Exceptions\NotFoundPayPasswordException;
use App\Exceptions\OutputServerMessageException;
use App\Models\TaskOrder;
use App\Models\User;
use App\Repositories\Eloquent\SendOrderRepositoryInterface;
use App\Repositories\Eloquent\SendOrderCarriageRepositoryInterface;
use App\Repositories\Eloquent\UserAllCouponRepositoryInterface;
use App\Repositories\Eloquent\UserCouponRepositoryInterface;
use App\Repositories\Eloquent\UserRepositoryInterface;
use App\Repositories\Eloquent\TaskOrderRepositoryInterface;
use App\Repositories\Eloquent\RemarkRepositoryInterface;
use App\Services\PayService;
use App\Models\SendOrderExpressCompany;
use App\Models\SendOrderItemType;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use Log;

class SendOrderController extends BaseController
{
    public function __construct(SendOrderRepositoryInterface $sendOrderRepository,
                                SendOrderCarriageRepositoryInterface $sendOrderCarriageRepository,
                                UserCouponRepositoryInterface $userCouponRepository,
                                UserAllCouponRepositoryInterface $userAllCouponRepository,
                                UserRepositoryInterface $userRepository,
                                TaskOrderRepositoryInterface $taskOrderRepository,
                                RemarkRepositoryInterface $remarkRepository,
                                PayService $payService)
    {
        parent::__construct();
        $this->middleware('auth.api',['except' => ['getExpressCompanies','getItemTypes']]);
        $this->userCouponRepository = $userCouponRepository;
        $this->userRepository = $userRepository;
        $this->taskOrderRepository = $taskOrderRepository;
        $this->sendOrderRepository = $sendOrderRepository;
        $this->sendOrderCarriageRepository = $sendOrderCarriageRepository;
        $this->userAllCouponRepository = $userAllCouponRepository;
        $this->payService = $payService;
    }
    public function getExpressCompanies(Request $request)
    {
        $express_companies = SendOrderExpressCompany::getExpressCompanies();
        return $this->response->success()->data($express_companies->toArray())->json();
    }
    public function getItemTypes(Request $request)
    {
        $item_types = SendOrderItemType::getItemTypes();
        return $this->response->success()->data($item_types->toArray())->json();
    }
    public function createOrder(Request $request)
    {
        $user = User::tokenAuth();
        $order_data = $request->all();
        $rule = [
            'urgent' => 'sometimes|in:0,1',
            'urgent_price' => 'sometimes|numeric|min:0',
            'payment' => "required|in:wechat,balance",
            "postscript" => 'sometimes',
            'consignee' => 'required',
            'consignee_mobile' => 'required|regex:'.config('regex.phone'),
            'consignee_address' => 'required',
            'sender' => 'required',
            'sender_mobile' => 'required|regex:'.config('regex.phone'),
            'sender_address' => 'required',
            "best_time" => 'required',
            'item_type_name' => 'required',
            'express_company_name' => 'required',
        ];
        validateCustomParameter($order_data,$rule);

        $urgent = !empty($order_data['urgent']) ? $order_data['urgent'] : 0;
        $order_price = setting('send_order_min_price');
        $urgent_price = 0;
        if($urgent)
        {
            $order_price = setting('urgent_min_price');
            $urgent_price = setting('urgent_min_price');
        }
        $original_price = $order_price;
        $total_price = $order_price;

        //骑手所得款项
        $deliverer_price = $order_price;

        $coupon_id = !empty($request->coupon_id) ? intval($request->coupon_id): 0;
        $coupon_price = 0;
        if($coupon_id)
        {
            $coupon_data = $this->userAllCouponRepository->useCoupon($user->id,$coupon_id,$total_price);
            $coupon_price = $coupon_data['price'];
            $total_price =  $total_price - $coupon_price;
        }

        if($order_data['payment'] == 'balance')
        {
            checkBalance($user,$total_price);
        }
        $order_sn = generate_order_sn('SEND-');
        $order_data = [
            'order_sn' => $order_sn,
            'user_id' => $user->id,
            'urgent' => $urgent,
            'urgent_price' => $urgent_price,
            'payment' => $order_data['payment'],
            'coupon_id' => $coupon_id,
            'coupon_name' => isset($coupon_data) && !empty($coupon_data) ? $coupon_data['name'] : '',
            'coupon_price' => $coupon_price,
            'deliverer_price' => $deliverer_price,
            'order_status' => 'unpaid',
            'postscript' => !empty($order_data['postscript']) ? $order_data['postscript'] : '',
            'total_price' => $total_price,
            'original_price' => $original_price ,
            'order_price' => $order_price,
            'consignee' => $order_data['consignee'],
            'consignee_mobile' => $order_data['consignee_mobile'],
            'consignee_address' => $order_data['consignee_address'],
            'sender' => $order_data['sender'],
            'sender_mobile' => $order_data['sender_mobile'],
            'sender_address' => $order_data['sender_address'],
            "best_time" => $order_data['best_time'],
            'item_type_name' => $order_data['item_type_name'],
            'express_company_name' => $order_data['express_company_name'],
        ];

        $order = $this->sendOrderRepository->create($order_data);
        $task_order = $this->taskOrderRepository->create([
            'order_sn' => $order_sn,
            'name' => '代寄',
            'user_id' => $user->id,
            'objective_id' => $order->id,
            'objective_model' => 'SendOrder',
            'type' => 'send_order',
        ]);

        $data = [
            'task_order_id' => $task_order->id,
            'send_order_id' => $order->id,
            'order_sn' => $order_sn,
            'body' => "发布代寄",
            'detail' => "发布代寄",
            'total_price' => $total_price,
            'trade_type' => 'CREATE_SEND_ORDER',
            'payment' => $request->payment,
            'pay_from' => 'SendOrder',
            'coupon_id' => $coupon_id,
            'coupon_price' => $coupon_price,
        ];
        $data = $this->payService->payHandle($data);

        return $this->response->success()->data($data)->json();
    }
    //运费 + 附加费
    public function payCarriage(Request $request)
    {
        $rule = [
            'id' => 'required|integer',
            'payment' => "required|in:wechat,balance",
        ];
        validateParameter($rule);

        $user = User::tokenAuth();

        $send_order = $this->sendOrderRepository->find($request->id);

        $send_order_carriage = $this->sendOrderCarriageRepository->where('send_order_id',$send_order->id)->first();
        if($send_order->user_id != $user->id){
            throw new PermissionDeniedException();
        }
        if($send_order->order_status != 'unpaid_carriage')
        {
            throw new OutputServerMessageException("该任务状态不支持支付");
        }
        $total_price = $send_order_carriage->total_price;
        if($request->payment == 'balance')
        {
            checkBalance($user,$total_price);
        }
        $this->sendOrderCarriageRepository->update(['payment' => $request->payment],$send_order_carriage->id);
        $data = [
            'send_order' => $send_order,
            'send_order_carriage_price_id' => $send_order_carriage->id,
            'order_sn' => $send_order_carriage->order_sn,
            'carriage' => $send_order_carriage->carriage,
            'extra_price' => $send_order_carriage->extra_price,
            'total_price' => $total_price,
            'body' => "代寄运费",
            'detail' => "代寄运费",
            'trade_type' => 'SEND_ORDER_CARRIAGE',
            'payment' => $request->payment,
            'pay_from' => 'SendOrderCarriagePrice',
        ];
        $data = $this->payService->payHandle($data);

        return $this->response->success()->data($data)->json();
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

        $send_order = $this->sendOrderRepository->find($request->id);

        if($send_order->user_id != $user->id)
        {
            throw new PermissionDeniedException();
        }

        $this->sendOrderRepository->completeOrder($send_order);

        throw new \App\Exceptions\RequestSuccessException("确认成功！");
    }

    public function cancelOrder(Request $request)
    {
        $rule = [
            'id' => 'required|integer',
        ];
        validateParameter($rule);

        $user = User::tokenAuth();

        $send_order = $this->sendOrderRepository->find($request->id);

        if($send_order->user_id != $user->id){
            throw new PermissionDeniedException('没有取消该任务的权限');
        }
        $this->sendOrderRepository->userCancelOrder($send_order);
        throw new \App\Exceptions\RequestSuccessException(trans("task_order.refund_success"));
    }

    public function agreeCancelOrder(Request $request)
    {
        $rule = [
            'id' => 'required|integer',
        ];
        validateParameter($rule);

        $user = User::tokenAuth();

        $send_order = $this->sendOrderRepository->find($request->id);

        if($send_order->user_id != $user->id){
            throw new PermissionDeniedException();
        }

        $this->sendOrderRepository->agreeCancelOrder($send_order);
        throw new \App\Exceptions\RequestSuccessException(trans("task_order.agree_cancel"));
    }
    public function disagreeCancelOrder(Request $request)
    {
        $rule = [
            'id' => 'required|integer',
        ];
        validateParameter($rule);

        $user = User::tokenAuth();

        $send_order = $this->sendOrderRepository->find($request->id);

        if($send_order->user_id != $user->id){
            throw new PermissionDeniedException();
        }

        $this->sendOrderRepository->disagreeCancelOrder($send_order);
        throw new \App\Exceptions\RequestSuccessException("驳回成功");
    }
}