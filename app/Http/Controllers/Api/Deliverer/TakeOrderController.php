<?php

namespace App\Http\Controllers\Api\Deliverer;

use App\Exceptions\OutputServerMessageException;
use App\Exceptions\PermissionDeniedException;
use App\Exceptions\RequestSuccessException;
use App\Repositories\Eloquent\UserRepositoryInterface;
use App\Repositories\Eloquent\TakeOrderRepositoryInterface;
use App\Repositories\Eloquent\TakeOrderExpressRepositoryInterface;
use App\Repositories\Eloquent\TakeOrderExtraPriceRepositoryInterface;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Api\BaseController;
use Log;

class TakeOrderController extends BaseController
{
    public function __construct(TakeOrderRepositoryInterface $takeOrderRepository,
                                TakeOrderExpressRepositoryInterface $takeOrderExpressRepository,
                                TakeOrderExtraPriceRepositoryInterface $takeOrderExtraPriceRepository,
                                UserRepositoryInterface $userRepository)
    {
        parent::__construct();
        $this->middleware('auth.api',['except' => []]);
        $this->takeOrderRepository = $takeOrderRepository;
        $this->takeOrderExpressRepository = $takeOrderExpressRepository;
        $this->takeOrderExtraPriceRepository = $takeOrderExtraPriceRepository;
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

        $this->checkDelivererPermission($take_order->deliverer_id);

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

        $this->checkDelivererPermission($take_order->deliverer_id);

        $this->takeOrderRepository->delivererCancelOrder($take_order);

        throw new \App\Exceptions\RequestSuccessException();
    }
    public function submitServicePrice(Request $request)
    {
        $rule = [
            'id' => 'required|integer',
            'service_price' => 'required|integer',
        ];
        validateParameter($rule);
        $total_price = $service_price = $request->service_price;
        $take_order = $this->takeOrderRepository->find($request->id);
        $this->checkDelivererPermission($take_order->deliverer_id);

        if ($take_order->order_status != 'accepted') {
            throw new \App\Exceptions\OutputServerMessageException('当前任务状态不允许该操作');
        }

        $take_order_extra_price = $this->takeOrderExtraPriceRepository->where('take_order_id',$take_order->id)->first(['id','status']);

        if($take_order_extra_price)
        {
            if($take_order_extra_price->status != 'unpaid')
            {
                throw new \App\Exceptions\OutputServerMessageException('已支付，不允许该操作');
            }
            $this->takeOrderExtraPriceRepository->update([
                'service_price' => $service_price,
                'total_price' => $total_price,
            ],$take_order_extra_price->id);
        }else{
            $order_sn = 'TAKEEXTRA-'.generate_order_sn();
            $this->takeOrderExtraPriceRepository->create([
                'order_sn' => $order_sn,
                'take_order_id' => $take_order->id,
                'service_price' => $service_price,
                'total_price' => $total_price,
                'status' => 'unpaid'
            ]);
        }
        throw new RequestSuccessException();
    }
    private function checkDelivererPermission($deliverer_id,$message="")
    {
        if($deliverer_id != $this->deliverer->id){
            throw new PermissionDeniedException($message);
        }
    }
}