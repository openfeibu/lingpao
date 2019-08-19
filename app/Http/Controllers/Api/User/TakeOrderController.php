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
use App\Services\PayService;
use Log,DB;
use Illuminate\Http\Request;

class TakeOrderController extends BaseController
{
    public function __construct(TakeOrderRepositoryInterface $takeOrderRepository,
                                TakeOrderExpressRepositoryInterface $takeOrderExpressRepository,
                                UserCouponRepositoryInterface $userCouponRepository,
                                UserRepositoryInterface $userRepository,
                                PayService $payService)
    {
        parent::__construct();
        $this->middleware('auth.api',['except' => ['extractExpressInfo','getOrders']]);
        $this->takeOrderRepository = $takeOrderRepository;
        $this->takeOrderExpressRepository = $takeOrderExpressRepository;
        $this->userCouponRepository = $userCouponRepository;
        $this->userRepository = $userRepository;
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

        $urgent = !empty($order_data['urgent']) ? $order_data['urgent'] : 0;
        $express_price = setting('take_order_min_price');
        $urgent_price = 0;
        if($urgent)
        {
            $express_price = setting('urgent_min_price');
            $urgent_price = setting('urgent_min_price');
        }
        $total_price = $express_price * $express_count + $tip;

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
    public function getOrders(Request $request)
    {
        $limit = $request->input('limit',config('app.limit'));
        $take_orders = TakeOrder::join('users','users.id','=','take_orders.user_id')
                ->whereIn('take_orders.order_status', ['new','accepted','finish','completed'])
                ->orderBy('status_num','asc')
                ->orderBy('take_orders.id','desc')
                ->select(DB::raw('take_orders.id,take_orders.order_sn,take_orders.user_id,take_orders.deliverer_id,take_orders.urgent,take_orders.total_price,express_count,take_orders.order_status,take_orders.created_at,CASE take_orders.order_status WHEN "new" THEN 1 ELSE 2 END as status_num,users.nickname,users.avatar_url'))
                ->paginate($limit);
        foreach ($take_orders as $key => $take_order)
        {
            $take_order->friendly_date = friendly_date($take_order->created_at);
            $take_order->expresses = $this->takeOrderExpressRepository->where('take_order_id',$take_order->id)
                ->orderBy('id','asc')->get(['take_place','address']);
        }
        $data = $take_orders->toArray()['data'];
        return $this->response->success()->count($take_orders->total())->data($data)->json();
    }
    public function getOrder(Request $request,$id)
    {
        $user = User::tokenAuth();
        $take_order = $this->takeOrderRepository->find($id,['id','order_sn','user_id','deliverer_id','urgent','urgent_price','tip','coupon_id','coupon_name','coupon_price','original_price','total_price','order_status','express_count','express_price','created_at']);
        $take_order->friendly_date = friendly_date($take_order->created_at);
        $take_order_data = $take_order->toArray();
        $take_order_expresses = $this->takeOrderExpressRepository->where('take_order_id',$take_order->id)
            ->orderBy('id','asc')
            ->get();

        if(in_array($take_order->order_status,['unpaid']))
        {
            throw OutputServerMessageException("该订单无效");
        }
//        if($take_order->user_id != $user->id)
//        {
//            throw new PermissionDeniedException();
//        }
        $take_order_user = $this->userRepository->find($take_order->user_id);
        $take_order_deliverer = $this->userRepository->where('id',$take_order->deliverer_id)->first();

        $user_field = ['id','avatar_url','nickname'];
        $take_order_expresses_field = ['take_place','address'];
        if($take_order->deliverer_id == $user->id || $take_order->user_id == $user->id)
        {
            $user_field = array_merge($user_field,['phone']);
            $take_order_expresses_field = ['take_place','consignee','mobile','address','description','take_code','express_company','express_arrive_date'];
        }
        if($take_order->order_status == 'new')
        {
            foreach ($take_order_expresses as $key => $take_order_express)
            {
                $take_order_expresses_data[] = visible_data($take_order_express->toArray(),$take_order_expresses_field);
            }
            $take_order_data['expresses'] = $take_order_expresses_data;
        }
        else{
            $take_order['expresses'] = $take_order_expresses->toArray();
        }

        $take_order_user_data = visible_data($take_order_user->toArray(),$user_field);
        $take_order_deliverer_data = $take_order_deliverer ? visible_data($take_order_deliverer->toArray(),$user_field) : [];
        $data = [
            'take_order' => $take_order_data,
            'user' => $take_order_user_data,
            'deliverer' => $take_order_deliverer_data
        ];
        return $this->response->success()->data($data)->json();
    }
}