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

    protected $appends = ['task_order_id','status_desc','service_price_data'];

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
            return trans('task.take_order.user_status_desc.'.$order_cancel_status);
        }
        return trans('task.take_order.user_status_desc.'.$order_status);
    }
    public function getServicePriceDataAttribute()
    {
        $id = $this->attributes['id'];
        $data = [
            'service_price' => 0,
            'service_price_pay_status' => '',
            'service_price_pay_status_desc' => ''
        ];
        $service = TakeOrderExtraPrice::where('take_order_id',$id)->first(['service_price','status','id']);
        if($service)
        {
            $data = [
                'service_price' => $service->service_price,
                'service_price_pay_status' => $service->status,
                'service_price_pay_status_desc' => trans('task.take_order.service_price_pay_status.'.$service->status)
            ];
        }
        return $data;
    }

    public function getServicePriceAttribute()
    {
        $id = $this->attributes['id'];
        $service_price = TakeOrderExtraPrice::where('take_order_id',$id)->value('service_price');
        return $service_price ?? 0;
    }
    public function getServicePricePayStatusAttribute()
    {
        $id = $this->attributes['id'];
        $status = TakeOrderExtraPrice::where('take_order_id',$id)->value('status');
        return $status ?? NULL;
    }
    public function getServicePricePayStatusDescAttribute()
    {
        $id = $this->attributes['id'];
        $status = TakeOrderExtraPrice::where('take_order_id',$id)->value('status');
        return $status ? trans('task.take_order.service_price_pay_status.'.$status) : '';
    }
}