<?php

namespace App\Models;

use DB,Hash,Auth,Request;
use App\Models\BaseModel;
use App\Traits\Database\Slugger;
use App\Traits\Database\DateFormatter;
use App\Traits\Filer\Filer;

class CustomOrderCategory extends BaseModel
{
    use Filer, Slugger, DateFormatter;

    protected $config = 'model.custom_order.custom_order_category';

    public $timestamps = false;

    public static function getCategories()
    {
        return self::orderBy('order','asc')->orderBy('id','asc')->get();
    }
}