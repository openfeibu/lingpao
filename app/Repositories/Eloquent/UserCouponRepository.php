<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\UserCouponRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Models\User;

class UserCouponRepository extends BaseRepository implements UserCouponRepositoryInterface
{

    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return config('model.coupon.user_coupon.model');
    }
    public function getAvailableCoupon($where,$min_price)
    {
        $coupon = $this->where($where)->where('min_price','<=', $min_price)->where('status','unused')->where('overdue','>',date('Y-m-d'))->first();
        if(!$coupon)
        {
            throw new \App\Exceptions\OutputServerMessageException('优惠券不存在或不可用');
        }
        return $coupon;
    }
}
