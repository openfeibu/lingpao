<?php

namespace App\Http\Controllers\Api\Deliverer;

use App\Exceptions\OutputServerMessageException;
use App\Exceptions\PermissionDeniedException;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Api\BaseController;
use Log;
use App\Repositories\Eloquent\UserRepositoryInterface;
use App\Repositories\Eloquent\TakeOrderRepositoryInterface;
use App\Repositories\Eloquent\TakeOrderExpressRepositoryInterface;

class TakeOrderController extends BaseController
{
    public function __construct(TakeOrderRepositoryInterface $takeOrderRepository,
                                TakeOrderExpressRepositoryInterface $takeOrderExpressRepository,
                                UserRepositoryInterface $userRepository)
    {
        parent::__construct();
        $this->middleware('auth.api',['except' => []]);
        $this->takeOrderRepository = $takeOrderRepository;
        $this->takeOrderExpressRepository = $takeOrderExpressRepository;
        $this->userRepository = $userRepository;
        $this->deliverer = User::tokenAuth();
    }
    public function acceptOrder(Request $request)
    {
        //    $this->messageService->SystemMessage2SingleOne(77, '取货码：1233');exit;
        //检验请求参数
        $fp = fopen("lock.txt", "w+");
        if (flock($fp, LOCK_NB | LOCK_EX)) {
            $rule = [
                'token' => 'required',
                'id' => 'required|integer',
            ];
            validateParameter($rule);

            //检验是否骑手
            User::isRole('deliverer');

            $take_order = $this->takeOrderRepository->find($request->id);
            //接受任务
            $this->takeOrderRepository->acceptOrder($take_order);

            return $this->response->success("恭喜，接单成功")->data(['task_order_id' => $take_order->task_order_id,'take_order_id' => $take_order->id])->json();
        }
        else {
            throw new \App\Exceptions\OutputServerMessageException('接单失败，系统繁忙！');
        }
        @fclose($fp);
    }

    /**
     * 接单人完成任务
     */
    public function finishOrder(Request $request)
    {
        //检验请求参数
        $rule = [
            'id' => 'required|integer',
        ];
        validateParameter($rule);

        $take_order = $this->takeOrderRepository->find($request->id);
        //检验任务是否已接
        if($take_order->deliverer_id != $this->deliverer->id)
        {
            throw new PermissionDeniedException();
        }
        if ($take_order->order_status != 'accepted') {
            throw new \App\Exceptions\OutputServerMessageException('当前任务状态不允许完成任务');
        }

        $this->takeOrderRepository->updateOrderStatus(['order_status' => 'finish'],$take_order->id);

        //TODO:通知 $take_order->user_id

        throw new \App\Exceptions\RequestSuccessException("恭喜，已完成任务！等待用户确认！");
    }
    public function cancelOrder(Request $request)
    {
        $rule = [
            'id' => 'required|integer',
        ];
        validateParameter($rule);

        $take_order = $this->takeOrderRepository->find($request->id);

        if($take_order->deliverer_id != $this->deliverer->id){
            throw new PermissionDeniedException('没有取消该任务的权限');
        }

        $this->takeOrderRepository->delivererCancelOrder($take_order);

        throw new \App\Exceptions\RequestSuccessException();

    }

}