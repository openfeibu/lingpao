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

}
