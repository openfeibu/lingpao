<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\PermissionDeniedException;
use App\Http\Controllers\Api\BaseController;
use App\Exceptions\NotFoundPayPasswordException;
use App\Exceptions\OutputServerMessageException;
use App\Models\CustomOrderCategory;
use App\Models\User;
use App\Repositories\Eloquent\CustomOrderRepositoryInterface;
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

        $user_coupon_id = !empty($request->coupon_id) ? intval($request->coupon_id): 0;
        if($user_coupon_id)
        {
            $coupon = $this->userCouponRepository->getAvailableCoupon(['user_id' => $user->id,'id' => $user_coupon_id],$total_price);
            $total_price =  $total_price - $coupon->price;
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
            'coupon_id' => $user_coupon_id,
            'coupon_name' => isset($coupon) && !empty($coupon) ? '满'.$coupon->min_price.'减'.$coupon->price : '',
            'coupon_price' => isset($coupon) && !empty($coupon) ? $coupon->price : 0,
            'deliverer_price' => $deliverer_price,
            'order_status' => 'unpaid',
            'best_time' => $request->best_time,
            'postscript' => !empty($order_data['postscript']) ? $order_data['postscript'] : '',
        ];
        $order = $this->customOrderRepository->create($order_data);
        $task_order = $this->taskOrderRepository->create([
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
            'user_coupon_id' => $user_coupon_id,
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

        $take_order = $this->customOrderRepository->find($request->id);

        if($take_order->user_id != $user->id)
        {
            throw new PermissionDeniedException();
        }

        $this->customOrderRepository->completeOrder($take_order);

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
    }
    //额外费用支付（服务费等）
    public function payServicePrice(Request $request)
    {

    }
}