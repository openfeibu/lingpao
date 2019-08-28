<?php

namespace App\Models;

use DB,Hash,Auth,Request;
use App\Models\BaseModel;
use App\Traits\Database\Slugger;
use App\Traits\Database\DateFormatter;
use App\Traits\Filer\Filer;

class UserAllCoupon extends BaseModel
{
    use Filer, Slugger, DateFormatter;

    protected $config = 'model.coupon.user_all_coupon';


}