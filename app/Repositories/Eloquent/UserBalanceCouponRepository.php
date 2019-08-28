<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\UserBalanceCouponRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Models\User;
use App\Models\UserBalanceCoupon;

class UserBalanceCouponRepository extends BaseRepository implements UserBalanceCouponRepositoryInterface
{

    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return config('model.coupon.user_balance_coupon.model');
    }

}
