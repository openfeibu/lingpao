<?php

namespace App\Models;

use DB,Hash,Auth,Request;
use App\Models\BaseModel;
use App\Traits\Database\Slugger;
use App\Traits\Database\DateFormatter;
use App\Traits\Filer\Filer;

class UserCoupon extends BaseModel
{
    use Filer, Slugger, DateFormatter;

    protected $config = 'model.coupon.user_coupon';

    protected $appends = ['status_desc'];

    public function getStatusDescAttribute()
    {
        $status = $this->attributes['status'];

        return trans('user_coupon.status.'.$status);
    }
}