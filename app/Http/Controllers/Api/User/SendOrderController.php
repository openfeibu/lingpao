<?php

namespace App\Http\Controllers\Api\User;

use App\Models\SendOrderExpressCompany;
use App\Models\SendOrderItemType;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use Log;

class SendOrderController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.api',['except' => ['getExpressCompanies','getItemTypes']]);
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
            'coupon_id' => $coupon_id,
            'coupon_name' => isset($coupon_data) && !empty($coupon_data) ? $coupon_data['name'] : '',
            'coupon_price' => $coupon_price,
            'deliverer_price' => $deliverer_price,
            'order_status' => 'unpaid',
            'postscript' => !empty($order_data['postscript']) ? $order_data['postscript'] : '',
        ];

        $order = $this->takeOrderRepository->create($order_data);
        $task_order = $this->taskOrderRepository->create([
            'order_sn' => $order_sn,
            'name' => '代拿',
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
            'coupon_id' => $coupon_id,
            'coupon_price' => $coupon_price,
        ];
        $data = $this->payService->payHandle($data);

        return $this->response->success()->data($data)->json();
    }
}