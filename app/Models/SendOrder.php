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

    protected $appends = ['task_order_id','carriage_data','status_desc','payment_desc','all_total_price'];

    public function getTaskOrderIdAttribute()
    {
        $id = $this->attributes['id'];
        return TaskOrder::where('type','send_order')->where('objective_id',$id)->value('id');
    }

    public function getCarriageDataAttribute()
    {
        $id = $this->attributes['id'];
        $data = [
            'carriage' => 0,
            'extra_price' => 0,
            'carriage_pay_status' => '',
            'carriage_pay_status_desc' => ''
        ];
        $carriage = SendOrderCarriage::where('send_order_id',$id)->first();
        if($carriage)
        {
            $data = [
                'carriage' => $carriage->carriage,
                'extra_price' => $carriage->extra_price,
                'carriage_pay_status' => $carriage->status,
                'carriage_pay_status_desc' => trans('task.send_order.carriage_pay_status.'.$carriage->status)
            ];
        }
        return $data;
    }

    public function getStatusDescAttribute()
    {
        $order_status = $this->attributes['order_status'];
        $order_cancel_status = $this->attributes['order_cancel_status'];
        if($order_status == 'cancel')
        {
            return trans('task.send_order.user_status_desc.'.$order_cancel_status);
        }
        return trans('task.send_order.user_status_desc.'.$order_status);
    }

    public function getPaymentDescAttribute()
    {
        return isset($this->attributes['payment']) ? trans('app.payment.payments.'.$this->attributes['payment']) : '';
    }

    public function getAllTotalPriceAttribute()
    {
        $id = $this->attributes['id'];
        $total_carriage = SendOrderCarriage::where('send_order_id',$id)->where('status','paid')->value('total_price');
        $all_total_price = $total_carriage ? $this->attributes['total_price'] +  $total_carriage : $this->attributes['total_price'];
        return $all_total_price;
    }
}