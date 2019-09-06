<?php
namespace App\Services;

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
use Log,DB;

class ScheduleService
{
    public function __construct(TakeOrderRepositoryInterface $takeOrderRepository,
                                CustomOrderRepositoryInterface $customOrderRepository,
                                RefundService $refundService)
    {
        $this->takeOrderRepository = $takeOrderRepository;
        $this->customOrderRepository = $customOrderRepository;
        $this->refundService = $refundService;
    }
    public function refund()
    {
        set_time_limit(0);
        $this->refundTakeOrder();
        $this->refundCustomOrder();
    }

    public function complete()
    {
        set_time_limit(0);
        $this->completeTakeOrder();
        $this->completeCustomOrder();
    }
    public function refundTakeOrder()
    {
        $take_orders = $this->takeOrderRepository
            ->where('created_at','<',date("Y-m-d 00:00:00"))
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
        return "success";
    }
    public function refundCustomOrder()
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
        return "success";
    }
    public function completeTakeOrder()
    {
        $take_orders = $this->takeOrderRepository
            ->join('task_order_status_changes as change','change.objective_id','=','take_orders.id')
            ->where('change.type','take_order')
            ->where('take_orders.order_status','finish')
            ->where('change.order_status','finish')
            ->where('change.created_at','<=',DB::raw('(select date_sub(now(), interval '.setting('task_auto_complete_hours'). ' HOUR))'))
            ->orderBy('take_orders.id','asc')
            ->get(['take_orders.*']);
        foreach ($take_orders as $key => $take_order)
        {
            $this->takeOrderRepository->completeOrder($take_order);
        }
        return "success";
    }
    public function completeCustomOrder()
    {
        $custom_orders = $this->customOrderRepository
            ->join('task_order_status_changes as change','change.objective_id','=','custom_orders.id')
            ->where('change.type','custom_order')
            ->where('custom_orders.order_status','finish')
            ->where('change.order_status','finish')
            ->where('change.created_at','<=',DB::raw('(select date_sub(now(), interval '.setting('task_auto_complete_hours'). ' HOUR))'))
            ->orderBy('custom_orders.id','asc')
            ->get(['custom_orders.*']);
        foreach ($custom_orders as $key => $custom_order)
        {
            $this->customOrderRepository->completeOrder($custom_order);
        }
        return "success";
    }

}