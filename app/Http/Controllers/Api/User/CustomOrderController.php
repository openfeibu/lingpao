<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\PermissionDeniedException;
use App\Http\Controllers\Api\BaseController;
use App\Exceptions\NotFoundPayPasswordException;
use App\Exceptions\OutputServerMessageException;
use App\Models\User;
use App\Models\CustomOrderType;
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
    public $takeOrderRepository;
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
        $this->middleware('auth.api',['except' => ['getTypes']]);
        $this->userCouponRepository = $userCouponRepository;
        $this->userRepository = $userRepository;
        $this->taskOrderRepository = $taskOrderRepository;
        $this->customOrderRepository = $customOrderRepository;
        $this->payService = $payService;
    }

    public function getTypes(Request $request)
    {
        $types = CustomOrderType::getTypes();
        return $this->response->success()->data($types->toArray())->json();
    }
    public function createOrder(Request $request)
    {
        $user = User::tokenAuth();
        $order_data = $request->all();
        $rule = [
            'type_id' => 'required|exists:custom_order_types,id',
            'tip' => 'sometimes|numeric|min:0',
            'payment' => "required|in:wechat,balance",
            "postscript" => 'sometimes|required|string',
        ];
        validateCustomParameter($order_data,$rule);

        $tip = !empty($order_data['tip']) ? $order_data['tip'] : 0;

        $deliverer_price = $tip;

        $total_price = $tip;

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
            'custom_order_type_id' => $request->type_id,
            'order_sn' => $order_sn,
            'user_id' => $user->id,
            'tip' => $tip,
            'payment' => $order_data['payment'],
            'total_price' => $total_price,
            'original_price' => $tip ,
            'coupon_id' => $user_coupon_id,
            'coupon_name' => isset($coupon) && !empty($coupon) ? '满'.$coupon->min_price.'减'.$coupon->price : '',
            'coupon_price' => isset($coupon) && !empty($coupon) ? $coupon->price : 0,
            'deliverer_price' => $deliverer_price,
            'order_status' => 'unpaid',
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

    }

    public function cancelOrder(Request $request)
    {


    }

    public function agreeCancelOrder(Request $request)
    {

    }
    //额外费用支付（服务费等）
    public function payServicePrice(Request $request)
    {

    }
}