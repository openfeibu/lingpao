<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\NotFoundPayPasswordException;
use App\Exceptions\OutputServerMessageException;
use App\Models\User;
use App\Repositories\Eloquent\UserCouponRepositoryInterface;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use App\Repositories\Eloquent\TakeOrderRepositoryInterface;
use App\Repositories\Eloquent\TakeOrderExpressRepositoryInterface;
use App\Services\PayService;
use Log;

class TakeOrderController extends BaseController
{
    public function __construct(TakeOrderRepositoryInterface $takeOrderRepository,
                                TakeOrderExpressRepositoryInterface $takeOrderExpressRepository,
                                UserCouponRepositoryInterface $userCouponRepository,
                                PayService $payService)
    {
        parent::__construct();
        $this->middleware('auth.api',['except' => ['extractExpressInfo']]);
        $this->takeOrderRepository = $takeOrderRepository;
        $this->takeOrderExpressRepository = $takeOrderExpressRepository;
        $this->userCouponRepository = $userCouponRepository;
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
        $urgent_price =  !empty($order_data['urgent_price']) ? $order_data['urgent_price'] * $express_count : 0;
        $total_price = setting('take_order_min_price') * $express_count + $urgent_price + $tip;

        $user_coupon_id = isset($request->coupon_id) ? intval($request->coupon_id): 0;
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
            'urgent' => !empty($order_data['urgent']) ? $order_data['urgent'] : 0,
            'urgent_price' => !empty($order_data['urgent_price']) ? $order_data['urgent_price'] : 0,
            'tip' => !empty($order_data['tip']) ? $order_data['tip'] : 0,
            'payment' => $order_data['payment'],
            'total_price' => $total_price,
            'express_count' => $express_count,
        ];

        $order = $this->takeOrderRepository->create($order_data);

        foreach ($expresses as $key => $express)
        {
            $express['take_order_id'] = $order->id;
            $this->takeOrderExpressRepository->create($express);
        }
        $data = [
            'order_id' => $order->id,
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
}