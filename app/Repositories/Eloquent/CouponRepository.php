<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\CouponRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class CouponRepository extends BaseRepository implements CouponRepositoryInterface
{
    public function model()
    {
        return config('model.coupon.coupon.model');
    }
}