<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use App\Models\UserCoupon;
use DB;
use Illuminate\Http\Request;
use App\Repositories\Eloquent\UserCouponRepositoryInterface;

class UserCouponController extends BaseController
{
    protected $user;

    public function __construct(UserCouponRepositoryInterface $userCouponRepository)
    {
        parent::__construct();
        $this->middleware('auth.api');
        $this->userCouponRepository = $userCouponRepository;
        $this->user = User::tokenAuth();
    }

    public function getUserCoupons(Request $request)
    {
        $user_coupons = $this->userCouponRepository
            ->where('user_id',$this->user->id)
            ->where('status','unused')
            ->where('overdue','>=',date('Y-m-d'))
            ->orderBy('price','desc')
            ->orderBy('id','desc')
            ->get();
        return $this->response->success()->data($user_coupons->toArray())->json();
    }
}
