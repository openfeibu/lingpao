<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\PermissionDeniedException;
use App\Http\Controllers\Api\BaseController;
use App\Exceptions\NotFoundPayPasswordException;
use App\Exceptions\OutputServerMessageException;
use App\Models\CustomOrderCategory;
use App\Models\TaskOrder;
use App\Models\User;
use App\Repositories\Eloquent\CustomOrderRepositoryInterface;
use App\Repositories\Eloquent\UserAllCouponRepositoryInterface;
use App\Repositories\Eloquent\UserCouponRepositoryInterface;
use App\Repositories\Eloquent\UserRepositoryInterface;
use App\Repositories\Eloquent\TaskOrderRepositoryInterface;
use App\Repositories\Eloquent\RemarkRepositoryInterface;
use App\Services\PayService;
use Log,DB;
use Illuminate\Http\Request;

class CustomOrderController extends BaseController
{
    public $customOrderRepository;
    public $takeOrderExpressRepository;
    public $userCouponRepository;
    public $userRepository;
    public $taskOrderRepository;
    public $payService;

    public function __construct(CustomOrderRepositoryInterface $customOrderRepository,
                                UserCouponRepositoryInterface $userCouponRepository,
                                UserAllCouponRepositoryInterface $userAllCouponRepository,
                                UserRepositoryInterface $userRepository,
                                TaskOrderRepositoryInterface $taskOrderRepository,
                                RemarkRepositoryInterface $remarkRepository,
                                PayService $payService)
    {
        parent::__construct();
        $this->middleware('auth.api',['except' => ['getCategories']]);
        $this->userCouponRepository = $userCouponRepository;
        $this->userRepository = $userRepository;
        $this->taskOrderRepository = $taskOrderRepository;
        $this->customOrderRepository = $customOrderRepository;
        $this->userAllCouponRepository = $userAllCouponRepository;
        $this->payService = $payService;
    }

    public function getCategories(Request $request)
    {
        $types = CustomOrderCategory::getCategories();
        return $this->response->success()->data($types->toArray())->json();
    }
    public function createOrder(Request $request)
    {
        $user = User::tokenAuth();
        $order_data = $request->all();
        $rule = [
            'category_id' => 'required|exists:custom_order_categories,id',
            'tip' => 'sometimes|numeric|min:',
            'payment' => "required|in:wechat,balance",
            "postscript" => 'sometimes|required|string',
            "best_time" => 'required',
        ];
        $messages = [
           'order_price.min' => '订单最低不能低于'.setting('custom_order_min_price'),
        ];
        validateCustomParameter($order_data,$rule,$messages);
        $original_price = $order_price = setting('custom_order_min_price');
        $tip = !empty($order_data['tip']) ? $order_data['tip'] : 0;

        $total_price  = $deliverer_price = $order_price + $tip;

        $coupon_id = !empty($request->coupon_id) ? intval($request->coupon_id): 0;
        $coupon_price = 0;
        if($coupon_id)
        {
            $coupon_data = $this->userAllCouponRepository->useCoupon($user->id,$coupon_id,$total_price);
            //$coupon = $this->userCouponRepository->getAvailableCoupon(['user_id' => $user->id,'id' => $user_coupon_id],$total_price);
            $coupon_price = $coupon_data['price'];
            $total_price =  $total_price - $coupon_price;
        }

        if($order_data['payment'] == 'balance')
        {
            checkBalance($user,$total_price);
        }
        $order_sn = generate_order_sn('CUSTOM-');

        $order_data = [
            'custom_order_category_id' => $request->category_id,
            'order_sn' => $order_sn,
            'user_id' => $user->id,
            'tip' => $tip,
            'payment' => $order_data['payment'],
            'total_price' => $total_price,
            'original_price' => $original_price ,
            'order_price' => $order_price,
            'coupon_id' => $coupon_id,
            'coupon_name' =>  isset($coupon_data) && !empty($coupon_data) ? $coupon_data['name'] : '',
            'coupon_price' => $coupon_price,
            'deliverer_price' => $deliverer_price,
            'order_status' => 'unpaid',
            'best_time' => $request->best_time,
            'postscript' => !empty($order_data['postscript']) ? $order_data['postscript'] : '',
        ];
        $order = $this->customOrderRepository->create($order_data);
        $task_order = $this->taskOrderRepository->create([
            'order_sn' => $order_sn,
            'name' => '帮帮忙',
            'user_id' => $user->id,
            'objective_id' => $order->id,
            'objective_model' => 'CustomOrder',
            'type' => 'custom_order',
        ]);
        $data = [
            'task_order_id' => $task_order->id,
            'custom_order_id' => $order->id,
            'order_sn' => $order_sn,
            'body' => "发布帮帮忙",
            'detail' => "发布帮帮忙",
            'total_price' => $total_price,
            'trade_type' => 'CREATE_CUSTOM_ORDER',
            'payment' => $request->payment,
            'pay_from' => 'CustomOrder',
            'coupon_id' => $coupon_id,
            'coupon_price' => $coupon_price,
        ];
        $data = $this->payService->payHandle($data);

        return $this->response->success()->data($data)->json();
    }


    public function getOrders(Request $request)
    {

    }
    public function getOrder(Request $request,$id)
    {

    }

    /**
     * 发单人结算任务
     */
    public function completeOrder(Request $request)
    {
        $rule = [
            'id' => 'required|integer',
        ];
        validateParameter($rule);

        $user = User::tokenAuth();

        $custom_order = $this->customOrderRepository->find($request->id);

        if($custom_order->user_id != $user->id)
        {
            throw new PermissionDeniedException();
        }

        $this->customOrderRepository->completeOrder($custom_order);

        throw new \App\Exceptions\RequestSuccessException("确认成功！");
    }

    public function cancelOrder(Request $request)
    {

        $rule = [
            'id' => 'required|integer',
        ];
        validateParameter($rule);

        $user = User::tokenAuth();

        $custom_order = $this->customOrderRepository->find($request->id);

        if($custom_order->user_id != $user->id){
            throw new PermissionDeniedException('没有取消该任务的权限');
        }
        $this->customOrderRepository->userCancelOrder($custom_order);
        throw new \App\Exceptions\RequestSuccessException(trans("task_order.refund_success"));
    }

    public function agreeCancelOrder(Request $request)
    {
        $rule = [
            'id' => 'required|integer',
        ];
        validateParameter($rule);

        $user = User::tokenAuth();

        $custom_order = $this->customOrderRepository->find($request->id);

        if($custom_order->user_id != $user->id){
            throw new PermissionDeniedException();
        }

        $this->customOrderRepository->agreeCancelOrder($custom_order);
        throw new \App\Exceptions\RequestSuccessException(trans("task_order.agree_cancel"));
    }
    public function disagreeCancelOrder(Request $request)
    {
        $rule = [
            'id' => 'required|integer',
        ];
        validateParameter($rule);

        $user = User::tokenAuth();

        $custom_order = $this->customOrderRepository->find($request->id);

        if($custom_order->user_id != $user->id){
            throw new PermissionDeniedException();
        }

        $this->customOrderRepository->disagreeCancelOrder($custom_order);
        throw new \App\Exceptions\RequestSuccessException("驳回成功");
    }
}