<?php

namespace App\Http\Controllers\Api;

use App\Events\GatewayWorker\Events;
use App\Exceptions\RequestSuccessException;
use App\Http\Controllers\Api\BaseController;
use App\Models\FormId;
use App\Models\User;
use App\Repositories\Eloquent\CustomOrderRepositoryInterface;
use App\Repositories\Eloquent\TakeOrderRepositoryInterface;
use App\Services\MessageService;
use App\Services\RefundService;
use Illuminate\Http\Request;
use App\Models\Banner;
use App\Models\Setting;
use Log;

class ScheduleController extends BaseController
{
    public function __construct(TakeOrderRepositoryInterface $takeOrderRepository,
                                CustomOrderRepositoryInterface $customOrderRepository,
                                RefundService $refundService)
    {
        parent::__construct();
        $this->takeOrderRepository = $takeOrderRepository;
        $this->customOrderRepository = $customOrderRepository;
        $this->refundService = $refundService;
    }
    public function refund()
    {
        $this->refundTakeOrder();
        $this->refundCustomOrder();
    }
    private function refundTakeOrder()
    {
        $take_orders = $this->takeOrderRepository
            ->where('created_at','<',date("Y-m-d 00:00:01"))
            ->where('order_status','new')
            ->orderBy('id','asc')
            ->get();
        foreach ($take_orders as $key => $take_order)
        {
            $data = [
                'id' => $take_order->id,
                'total_price' => $take_order->total_price,
                'order_sn' => $take_order->order_sn,
                'payment' => $take_order->payment,
                'coupon_id' => $take_order->coupon_id,
                'coupon_price' => $take_order->coupon_price,
                'trade_type' => 'CANCEL_TAKE_ORDER',
                'description' => '取消代拿任务',
            ];
            $user = User::getUserById($take_order->user_id);
            $this->refundService->refundHandle($data,'TakeOrder',$user);
        }

    }
    private function refundCustomOrder()
    {
        $custom_orders = $this->customOrderRepository
            ->where('created_at','<',date("Y-m-d 00:00:01"))
            ->where('order_status','new')
            ->orderBy('id','asc')
            ->get();
        foreach ($custom_orders as $key => $custom_order)
        {
            $data = [
                'id' => $custom_order->id,
                'total_price' => $custom_order->total_price,
                'order_sn' =>  $custom_order->order_sn,
                'payment' => $custom_order->payment,
                'coupon_id' => $custom_order->coupon_id,
                'coupon_price' => $custom_order->coupon_price,
                'trade_type' => 'CANCEL_CUSTOM_ORDER',
                'description' => '取消帮帮忙任务',
            ];
            $user = User::getUserById($custom_order->user_id);
            $this->refundService->refundHandle($data,'CustomOrder',$user);
        }
    }
}
