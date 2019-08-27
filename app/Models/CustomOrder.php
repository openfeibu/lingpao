<?php

namespace App\Models;

use DB,Hash,Auth,Request;
use App\Models\BaseModel;
use App\Traits\Database\Slugger;
use App\Traits\Database\DateFormatter;
use App\Traits\Filer\Filer;

class CustomOrder extends BaseModel
{
    use Filer, Slugger, DateFormatter;

    protected $config = 'model.custom_order.custom_order';

    protected $appends = ['custom_order_category_name'];

    public function getBestTimeAttribute($value)
    {
        return date('H:i',strtotime($value));
    }
    public function getCustomOrderCategoryNameAttribute()
    {
        $category_id = $this->attributes['custom_order_category_id'];

        return CustomOrderCategory::where('id',$category_id)->value('name');
    }
}