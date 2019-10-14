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
use App\Repositories\Eloquent\CustomOrderRepositoryInterface;
use App\Repositories\Eloquent\RemarkRepositoryInterface;
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
                                TaskOrderRepositoryInterface $taskOrderRepository,
                                CustomOrderRepositoryInterface $customOrderRepository,
                                RemarkRepositoryInterface $remarkRepository)
    {
        parent::__construct();
        $this->middleware('auth.api',['except' => ['getOrders']]);
        $this->takeOrderRepository = $takeOrderRepository;
        $this->takeOrderExpressRepository = $takeOrderExpressRepository;
        $this->userRepository = $userRepository;
        $this->taskOrderRepository = $taskOrderRepository;
        $this->remarkRepository = $remarkRepository;
        $this->customOrderRepository = $customOrderRepository;
    }
    public function getOrders(Request $request)
    {
        $orders_data = $this->taskOrderRepository->getTaskOrders();
        return $this->response->success()->count($orders_data['count'])->data($orders_data['data'])->json();
    }
    public function getOrder(Request $request,$id)
    {
        $order = $this->taskOrderRepository->find($id);
        switch ($order->type)
        {
            case 'take_order':
                $order_detail = $this->takeOrderRepository->getOrderDetail($order->objective_id);
                break;
            case 'custom_order':
                $order_detail = $this->customOrderRepository->getOrderDetail($order->objective_id);
               break;
            case 'send_order':
                $order_detail = $this->sendOrderRepository->getOrderDetail($order->objective_id);
                break;
        }

        $order_detail['type'] = $order->type;
        return $this->response->success()->data($order_detail)->json();
    }
    public function getUserOrders(Request $request)
    {
        $user = User::tokenAuth();
        $orders_data = $this->taskOrderRepository->getUserTaskOrders(['user_id' => $user->id]);
        return $this->response->success()->count($orders_data['count'])->data($orders_data['data'])->json();
    }
    public function getDelivererOrders(Request $request)
    {
        $user = User::tokenAuth();
        $orders_data = $this->taskOrderRepository->getUserTaskOrders(['deliverer_id' => $user->id]);
        return $this->response->success()->count($orders_data['count'])->data($orders_data['data'])->json();
    }
    public function remark(Request $request)
    {
        $rule = [
            'task_order_id' => 'required|integer',
            'service_grade' => 'required|between:0,5',
            'speed_grade' => 'required|between:0,5',
            'comment' => 'sometimes',
        ];
        validateParameter($rule);

        $user = User::tokenAuth();

        $task_order = $this->taskOrderRepository->find($request->task_order_id);

        if($task_order->user_id != $user->id){
            throw new PermissionDeniedException();
        }

        $remark = $this->remarkRepository->where('task_order_id',$task_order->id)->first(['id']);
        if($remark)
        {
            throw new \App\Exceptions\OutputServerMessageException('已评价，请勿重复提交');
        }

        if ($task_order->order_status != 'completed')
        {
            throw new \App\Exceptions\OutputServerMessageException('该任务未结算，请结算后再评价');
        }
        $remark = $this->remarkRepository->create([
            'user_id' => $user->id,
            'deliverer_id' => $task_order->deliverer_id,
            'service_grade' => $request->service_grade,
            'speed_grade' => $request->speed_grade,
            'comment' => isset($request->comment) && !empty($request->comment) ? $request->comment : '' ,
            'task_order_id' => $task_order->id,
        ]);
        $this->taskOrderRepository->updateOrderStatus(['order_status' => 'remarked'],$task_order->id);
        return $this->response->success("评价成功")->data(['remark_id' => $remark->id])->json();
    }
}