<?php

namespace App\Models;

use DB,Hash,Auth,Request;
use App\Models\BaseModel;
use App\Traits\Database\Slugger;
use App\Traits\Database\DateFormatter;
use App\Traits\Filer\Filer;

class TakeOrderExtraPrice extends BaseModel
{
    use Filer, Slugger, DateFormatter;

    protected $config = 'model.take_order.take_order_extra_price';


}