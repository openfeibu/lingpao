<?php

namespace App\Http\Controllers\Api\Deliverer;

use App\Exceptions\OutputServerMessageException;
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

}