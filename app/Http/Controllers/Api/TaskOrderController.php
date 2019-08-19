<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\PermissionDeniedException;
use App\Http\Controllers\Api\BaseController;
use App\Exceptions\NotFoundPayPasswordException;
use App\Exceptions\OutputServerMessageException;
use App\Models\TaskOrder;
use App\Models\User;
use App\Models\TakeOrder;
use App\Repositories\Eloquent\UserRepositoryInterface;
use App\Repositories\Eloquent\TakeOrderRepositoryInterface;
use App\Repositories\Eloquent\TakeOrderExpressRepositoryInterface;
use App\Repositories\Eloquent\TaskOrderRepositoryInterface;
use Log,DB;
use Illuminate\Http\Request;

class TaskOrderController extends BaseController
{
    public $takeOrderRepository;
    public $takeOrderExpressRepository;
    public $userRepository;
    public $taskOrderRepository;
    public $payService;

    public function __construct(TakeOrderRepositoryInterface $takeOrderRepository,
                                TakeOrderExpressRepositoryInterface $takeOrderExpressRepository,
                                UserRepositoryInterface $userRepository,
                                TaskOrderRepositoryInterface $taskOrderRepository)
    {
        parent::__construct();
        $this->middleware('auth.api',['except' => ['getOrders']]);
        $this->takeOrderRepository = $takeOrderRepository;
        $this->takeOrderExpressRepository = $takeOrderExpressRepository;
        $this->userRepository = $userRepository;
        $this->taskOrderRepository = $taskOrderRepository;
    }
    public function getOrders(Request $request)
    {
        $type = $request->input('type','all');
        $limit = $request->input('limit',config('app.limit'));
        $orders = TaskOrder::select(DB::raw('*,CASE order_status WHEN "new" THEN 1 ELSE 2 END as status_num'));
        if($type != 'all')
        {
            $orders->where('type',$type);
        }
        $orders = $orders->orderBy('status_num','asc')
            ->orderBy('id','desc')
            ->paginate($limit);

        $orders_data = [];
        foreach ($orders as $key => $order)
        {
            if($order->type == 'take_order')
            {
                $order_detail = $this->takeOrderRepository->getOrder($order->objective_id);
            }
            $order_detail->task_order_id = $order->id;
            $order_detail->type = $order->type;
            $orders_data[] = $order_detail;
        }

        return $this->response->success()->count($orders->total())->data($orders_data)->json();
    }
    public function getOrder(Request $request,$id)
    {
        $order = $this->taskOrderRepository->find($id);
        if($order->type == 'take_order')
        {
            $order_detail = $this->takeOrderRepository->getOrderDetail($order->objective_id);
        }
        $order_detail['type'] = $order->type;
        return $this->response->success()->data($order_detail)->json();
    }
}