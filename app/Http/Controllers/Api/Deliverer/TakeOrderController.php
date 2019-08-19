<?php

namespace App\Http\Controllers\Api\Deliverer;

use Illuminate\Http\Request;
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
        $this->deliverer = Auth::user();
    }
    public function claimOrder(Request $request)
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

            //检验是否已实名
            $this->userRepository->isRealnameAuth($this->deliverer);

            //接受任务
            $this->takeOrderRepository->claimOrder(['id' => $request->id]);

            $order = $this->orderService->getSingleOrderAllInfo($request->order_id);

            $this->messageService->SystemMessage2SingleOne($order->owner_id, trans('task.task_be_accepted'));

            //推送给发单者

            $data = [
                'refresh' => 1,
                'target' => '',
                'open' => 'task',
                'data' => [
                    'id' => $request->order_id,
                    'title' => '校汇任务',
                    'content' => trans('task.task_be_accepted'),
                ],
            ];
            $this->pushService->PushUserTokenDevice('校汇任务', trans('task.task_be_accepted'), $order->owner_id,2,$data);
            throw new \App\Exceptions\RequestSuccessException("恭喜，接单成功！");
        }
        else {
            throw new \App\Exceptions\OutputServerMessageException('接单失败，系统繁忙！');
        }
        @fclose($fp);
    }

}