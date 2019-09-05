<?php

namespace App\Http\Controllers\Api;

use App\Events\GatewayWorker\Events;
use App\Exceptions\RequestSuccessException;
use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use App\Models\Coupon;
use App\Models\UserCoupon;
use App\Repositories\Eloquent\UserCouponRepositoryInterface;
use Illuminate\Http\Request;
use Log;

class CouponController extends BaseController
{
    public function __construct(UserCouponRepositoryInterface $userCouponRepository)
    {
        parent::__construct();
        $this->middleware('auth.api',['except' => []]);
        $this->userCouponRepository = $userCouponRepository;
    }
    public function coupon()
    {
        $coupons = Coupon::where('is_open',1)
            ->where('stock','>',0)
            ->select('id','name','price','min_price','stock','num','end_day')
            ->orderBy('id','asc')
            ->get()
            ->toArray();
        foreach ($coupons as $key => $coupon)
        {
            $user_coupon = UserCoupon::where('coupon_id',$coupon['id'])->first(['id']);
            $coupons[$key]['stock_rate'] = (round($coupon['stock'] / $coupon['num'],2) * 100).'%';
            if($user_coupon)
            {
                unset($coupons[$key]);
            }
        }
        return $this->response->success()->data($coupons)->json();
    }
    public function receiveCoupon(Request $request)
    {
        $user = User::tokenAuth();
        $data = $request->all();
        $rule = [
            'coupon_id' => 'required|exists:coupons,id',
        ];
        validateCustomParameter($data,$rule);
        $coupon = Coupon::where('id',$request->coupon_id)->first();
        if(!$coupon->is_open)
        {
            throw new \App\Exceptions\OutputServerMessageException('该优惠券暂未开放');
        }
        if(!$coupon->stock)
        {
            throw new \App\Exceptions\OutputServerMessageException('该优惠券已领取完');
        }
        $user_coupon = UserCoupon::where('coupon_id',$coupon['id'])->first(['id']);
        if($user_coupon)
        {
            throw new \App\Exceptions\OutputServerMessageException('请勿重复领该优惠券');
        }
        $this->userCouponRepository->createUserCoupon([
            'user_id' => $user->id,
            'coupon_id' => $coupon->id,
            'receive' => date('Y-m-d'),
            'min_price' => $coupon->min_price,
            'price' => $coupon->price,
            'overdue' => date('Y-m-d',strtotime('+'.$coupon->end_day.' day')),
        ]);
        Coupon::where('id',$request->coupon_id)->increment('receive_num');
        Coupon::where('id',$request->coupon_id)->decrement('stock');
        throw new \App\Exceptions\RequestSuccessException("领取成功！");
    }
}
