<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\UserCouponRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Models\User;
use App\Models\UserBalanceCoupon;

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
    public function createUserCoupon($data)
    {
        $user_coupon = $this->create($data);
        app(UserAllCouponRepository::class)->create([
            'user_id' => $data['user_id'],
            'type' => 'common',
            'objective_model' => 'UserCoupon',
            'objective_id' => $user_coupon->id,
        ]);
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
