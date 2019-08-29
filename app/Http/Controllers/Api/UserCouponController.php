<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use App\Models\UserBalanceCoupon;
use App\Models\UserCoupon;
use App\Repositories\Eloquent\UserBalanceCouponRepositoryInterface;
use DB;
use Illuminate\Http\Request;
use App\Repositories\Eloquent\UserCouponRepositoryInterface;

class UserCouponController extends BaseController
{
    protected $user;

    public function __construct(UserCouponRepositoryInterface $userCouponRepository,
                                UserBalanceCouponRepositoryInterface $userBalanceCouponRepository)
    {
        parent::__construct();
        $this->middleware('auth.api');
        $this->userCouponRepository = $userCouponRepository;
        $this->userBalanceCouponRepository = $userBalanceCouponRepository;
        $this->user = User::tokenAuth();
    }

    public function getUserCoupons(Request $request)
    {
        $user_balance_coupon_data = [];
        $user_balance_coupon = app(UserBalanceCoupon::class)
            ->select(DB::raw("uac.id,user_balance_coupons.id as user_balance_coupon_id,user_balance_coupons.user_id, user_balance_coupons.price, user_balance_coupons.balance,'balance_coupon' as type"))
            ->join('user_all_coupons as uac','uac.objective_id','=','user_balance_coupons.id')
            ->where('user_balance_coupons.user_id',$this->user->id)
            ->where('uac.objective_model','UserBalanceCoupon')
            ->first();
        if($user_balance_coupon)
        {
            $user_balance_coupon_data = $user_balance_coupon->toArray();
            $user_balance_coupon_data['balance_coupon_rate'] = setting('balance_coupon_rate');
        }

        $user_coupons = app(UserCoupon::class)
            ->select(DB::raw("uac.id,user_coupons.id as user_coupon_id,user_coupons.user_id, user_coupons.price,user_coupons.min_price,user_coupons.receive,user_coupons.overdue,user_coupons.status,'common_coupon' as type"))
            ->join('user_all_coupons as uac','uac.objective_id','=','user_coupons.id')
            ->where('uac.objective_model','UserCoupon')
            ->where('user_coupons.user_id',$this->user->id)
            ->where('status','unused')
            ->where('overdue','>=',date('Y-m-d'))
            ->orderBy('price','desc')
            ->orderBy('user_coupons.id','desc')
            ->get();
        $user_coupons_data = $user_coupons->toArray();

        $user_balance_coupon_data ? array_unshift($user_coupons_data,$user_balance_coupon_data) : '';

        return $this->response->success()->data($user_coupons_data)->json();
    }
}
