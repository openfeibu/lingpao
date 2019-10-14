<?php

namespace App\Models;

use DB,Hash,Auth,Request;
use App\Models\BaseModel;
use App\Traits\Database\Slugger;
use App\Traits\Database\DateFormatter;
use App\Traits\Filer\Filer;

class SendOrder extends BaseModel
{
    use Filer, Slugger, DateFormatter;

    protected $config = 'model.send_order.send_order';

    protected $appends = ['task_order_id','status_desc','payment_desc'];

    public function getTaskOrderIdAttribute()
    {
        $id = $this->attributes['id'];
        return TaskOrder::where('type','take_order')->where('objective_id',$id)->value('id');
    }

    public function getStatusDescAttribute()
    {
        $order_status = $this->attributes['order_status'];
        $order_cancel_status = $this->attributes['order_cancel_status'];
        if($order_status == 'cancel')
        {
            return trans('task_order.user_status_desc.'.$order_cancel_status);
        }
        return trans('task_order.user_status_desc.'.$order_status);
    }

    public function getPaymentDescAttribute()
    {
        return isset($this->attributes['payment']) ? trans('app.payment.payments.'.$this->attributes['payment']) : '';
    }
}