<?php

namespace App\Models;

use DB,Hash,Auth,Request;
use App\Models\BaseModel;
use App\Traits\Database\Slugger;
use App\Traits\Database\DateFormatter;
use App\Traits\Filer\Filer;

class CustomOrderType extends BaseModel
{
    use Filer, Slugger, DateFormatter;

    protected $config = 'model.custom_order.custom_order_type';

    public $timestamps = false;

    public static function getTypes()
    {
        return self::orderBy('order','asc')->orderBy('id','asc')->get();
    }
}