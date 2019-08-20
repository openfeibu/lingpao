<?php

namespace App\Models;

use DB,Hash,Auth,Request;
use App\Models\BaseModel;
use App\Traits\Database\Slugger;
use App\Traits\Database\DateFormatter;
use App\Traits\Filer\Filer;

class TakeOrder extends BaseModel
{
    use Filer, Slugger, DateFormatter;

    protected $config = 'model.take_order.take_order';

    protected $appends = ['task_order_id'];

    public function getTaskOrderIdAttribute()
    {
        $id = $this->attributes['id'];
        return TaskOrder::where('type','take_order')->where('objective_id',$id)->value('id');
    }


}