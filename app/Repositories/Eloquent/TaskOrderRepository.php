<?php

namespace App\Repositories\Eloquent;

use App\Models\TaskOrderStatusChange;
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
        $orders = $this->model->select(DB::raw('*,CASE order_status WHEN "new" THEN 1 WHEN "accepted" THEN 2 ELSE 3 END as status_num'))
            ->whereIn('type',['take_order','custom_order'])
            //->whereIn('order_status', ['new','accepted']);
            ->whereNotIn('order_status', ['unpaid']);
        if($type != 'all')
        {
            $orders = $orders->where('type',$type);
        }
        if($where)
        {
            $orders = $orders->where($where);
        }
        $orders = $orders
            ->where('created_at','>',date("Y-m-d 00:00:00"))
            ->orderBy('status_num','asc')
            ->orderBy('id','desc')
            ->paginate($limit);

        $orders_data = [];
        foreach ($orders as $key => $order)
        {
            switch ($order->type)
            {
                case 'take_order':
                    $order_detail = app(TakeOrderRepository::class)->getOrder($order->objective_id);
                    break;
                case 'custom_order':
                    $order_detail = app(CustomOrderRepository::class)->getOrder($order->objective_id);
                    break;
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
            ->whereNotIn('order_status', ['unpaid'])
            ->orderBy('status_num','asc')
            ->orderBy('id','desc')
            ->paginate($limit);

        $orders_data = [];
        foreach ($orders as $key => $order)
        {
            switch ($order->type)
            {
                case 'take_order':
                    $order_detail = app(TakeOrderRepository::class)->getOrder($order->objective_id);
                    break;
                case 'custom_order':
                    $order_detail = app(CustomOrderRepository::class)->getOrder($order->objective_id);
                    break;
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
    public function updateOrderStatus($data,$id)
    {
        $this->update($data,$id);
        $task_order = $this->find($id);
        $objective_data = [
            'order_status' => $data['order_status'],
        ];
        $change_data =  [
            'type' => $task_order->type,
            'user_id' => $task_order->user_id,
            'deliverer_id' => $task_order->deliverer_id,
            'objective_model' => $task_order->objective_model,
            'objective_id' => $task_order->objective_id,
            'order_status' => $data['order_status']
        ];
        if(isset($data['order_cancel_status']))
        {
            $objective_data['order_cancel_status'] = $change_data['order_cancel_status'] = $data['order_cancel_status'];
        }
        get_task_objective_model($task_order->objective_model)->where('id',$task_order->objective_id)->update($objective_data,$task_order->objective_id);
        TaskOrderStatusChange::create($change_data);
    }

    public function getAdminTaskOrders($type)
    {
        $limit = Request::get('limit',config('app.limit'));
        $search = Request::input('search',['order_status'=>'','search_user'=>'','search_deliverer'=>'','order_sn'=>'']);
        $orders = $this->model->join('users','users.id','=','task_orders.user_id')
            ->leftJoin('users as deliverer','deliverer.id','=','task_orders.deliverer_id')
            ->select(DB::raw('task_orders.*'))
            ->whereNotIn('task_orders.order_status', ['unpaid'])
            ->when($type, function ($query) use ($type) {
                return $query->where('task_orders.type', $type);
            })
            ->when($search['order_status'], function ($query) use ($search) {
                return $query->where('order_status', $search['order_status']);
            })
            ->when($search['order_sn'], function ($query) use ($search) {
                return $query->where('order_sn', $search['order_sn']);
            })
            ->when($search['search_user'], function ($query) use ($search) {
                return $query->where(function($query) use ($search) {
                    return $query->where('users.id', $search['search_user'])->orWhere('users.nickname','like','%'.$search['search_user'].'%')->orWhere('users.phone','like','%'.$search['search_user'].'%');
                });
            })
            ->when($search['search_deliverer'], function ($query) use ($search) {
                return $query->where(function($query) use ($search) {
                    return $query->where('deliverer.id', $search['search_deliverer'])->orWhere('deliverer.nickname','like','%'.$search['search_deliverer'].'%')->orWhere('deliverer.phone','like','%'.$search['search_deliverer'].'%');
                });
            });
        $orders = $orders->orderBy('task_orders.id','desc')->paginate($limit);

        $orders_data = [];
        foreach ($orders as $key => $order)
        {
            switch ($order->type)
            {
                case 'take_order':
                    $order_detail = app(TakeOrderRepository::class)->getAdminOrder($order->objective_id);
                    break;
                case 'custom_order':
                    $order_detail = app(CustomOrderRepository::class)->getAdminOrder($order->objective_id);
                    break;
            }

            $order_detail->task_order_id = $order->id;
            $order_detail->type = $order->type;
            $orders_data[] = $order_detail->toArray();
        }
        return [
            'data' => $orders_data,
            'count' => $orders->total()
        ];
    }
}
