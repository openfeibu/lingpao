<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\TaskOrderRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Models\User;
use Request,DB;

class TaskOrderRepository extends BaseRepository implements TaskOrderRepositoryInterface
{

    /**
     * Booting the repository.
     *
     * @return null
     */
    /*
    public function boot()
    {
        $this->fieldSearchable = config('model.user.user_address.search');
    }
    */

    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return config('model.task_order.task_order.model');
    }
    public function getTaskOrders($where=[])
    {
        $type = Request::get('type','all');
        $limit = Request::get('limit',config('app.limit'));
        $orders = $this->model->select(DB::raw('*,CASE order_status WHEN "new" THEN 1 ELSE 2 END as status_num'))
            ->whereIn('order_status', ['new','accepted']);
        if($type != 'all')
        {
            $orders = $orders->where('type',$type);
        }
        if($where)
        {
            $orders = $orders->where($where);
        }
        $orders = $orders
            //->where('created_at','>',date("Y-m-d 00:00:00"))
            ->orderBy('status_num','asc')
            ->orderBy('id','desc')
            ->paginate($limit);

        $orders_data = [];
        foreach ($orders as $key => $order)
        {
            if($order->type == 'take_order')
            {
                $order_detail = app(TakeOrderRepository::class)->getOrder($order->objective_id);
            }
            $order_detail->task_order_id = $order->id;
            $order_detail->type = $order->type;
            $orders_data[] = $order_detail;
        }
        return [
            'data' => $orders_data,
            'count' => $orders->total()
        ];
    }
    public function getUserTaskOrders($where)
    {
        $type = Request::get('type','all');
        $limit = Request::get('limit',config('app.limit'));
        $orders = $this->model->select(DB::raw('*,CASE order_status WHEN "new" THEN 1 ELSE 2 END as status_num'));
        if($type != 'all')
        {
            $orders = $orders->where('type',$type);
        }
        if($where)
        {
            $orders = $orders->where($where);
        }
        $orders = $orders
            ->orderBy('status_num','asc')
            ->orderBy('id','desc')
            ->paginate($limit);

        $orders_data = [];
        foreach ($orders as $key => $order)
        {
            if($order->type == 'take_order')
            {
                $order_detail = app(TakeOrderRepository::class)->getOrder($order->objective_id);
            }
            $order_detail->task_order_id = $order->id;
            $order_detail->type = $order->type;
            $orders_data[] = $order_detail;
        }
        return [
            'data' => $orders_data,
            'count' => $orders->total()
        ];
    }
    public function getTaskOrder($type,$objective_id)
    {
        return $this->where('type',$type)->where('objective_id',$objective_id)->first();
    }

}
