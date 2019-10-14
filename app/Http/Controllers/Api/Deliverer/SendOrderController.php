<?php

namespace App\Http\Controllers\Api\Deliverer;

use App\Exceptions\OutputServerMessageException;
use App\Exceptions\PermissionDeniedException;
use App\Exceptions\RequestSuccessException;
use App\Repositories\Eloquent\UserRepositoryInterface;
use App\Repositories\Eloquent\SendOrderRepositoryInterface;
use App\Repositories\Eloquent\SendOrderExtraPriceRepositoryInterface;
use App\Services\MessageService;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Api\BaseController;
use Log;

class SendOrderController extends BaseController
{
    public function __construct(SendOrderRepositoryInterface $sendOrderRepository,
                                SendOrderExtraPriceRepositoryInterface $sendOrderExtraPriceRepository,
                                UserRepositoryInterface $userRepository)
    {
        parent::__construct();
        $this->middleware('auth.api',['except' => []]);
        $this->sendOrderRepository = $sendOrderRepository;
        $this->sendOrderExtraPriceRepository = $sendOrderExtraPriceRepository;
        $this->userRepository = $userRepository;
        $this->deliverer = User::tokenAuth();
    }
    public function acceptOrder(Request $request)
    {
        $fp = fopen("lock.txt", "w+");
        if (flock($fp, LOCK_NB | LOCK_EX)) {
            $rule = [
                'token' => 'required',
                'id' => 'required|integer',
            ];
            validateParameter($rule);

            //检验是否骑手
            User::IsDeliverer();

            $send_order = $this->sendOrderRepository->find($request->id);
            //接受任务
            $this->sendOrderRepository->acceptOrder($send_order);

            return $this->response->success("恭喜，接单成功")->data(['task_order_id' => $send_order->task_order_id,'send_order_id' => $send_order->id])->json();
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

        $send_order = $this->sendOrderRepository->find($request->id);

        $this->checkDelivererPermission($send_order->deliverer_id);

        $this->sendOrderRepository->finishOrder($send_order);

        throw new \App\Exceptions\RequestSuccessException("恭喜，已完成任务！等待用户确认！");
    }
    public function cancelOrder(Request $request)
    {
        $rule = [
            'id' => 'required|integer',
        ];
        validateParameter($rule);

        $send_order = $this->sendOrderRepository->find($request->id);

        $this->checkDelivererPermission($send_order->deliverer_id);

        $this->sendOrderRepository->delivererCancelOrder($send_order);

        throw new \App\Exceptions\RequestSuccessException("操作成功，请等待或联系用户操作！");
    }
    public function submitServicePrice(Request $request)
    {
        $rule = [
            'id' => 'required|integer',
            'service_price' => 'required|integer',
        ];
        validateParameter($rule);
        $total_price = $service_price = $request->service_price;
        $send_order = $this->sendOrderRepository->find($request->id);
        $this->checkDelivererPermission($send_order->deliverer_id);

        if ($send_order->order_status != 'accepted') {
            throw new \App\Exceptions\OutputServerMessageException('当前任务状态不允许该操作');
        }

        $send_order_extra_price = $this->sendOrderExtraPriceRepository->where('send_order_id',$send_order->id)->first(['id','status']);

        if($send_order_extra_price)
        {
            if($send_order_extra_price->status != 'unpaid')
            {
                throw new \App\Exceptions\OutputServerMessageException('已支付，不允许该操作');
            }
            $this->sendOrderExtraPriceRepository->update([
                'service_price' => $service_price,
                'total_price' => $total_price,
            ],$send_order_extra_price->id);
        }else{
            $order_sn = 'TAKEEXTRA-'.generate_order_sn();
            $this->sendOrderExtraPriceRepository->create([
                'order_sn' => $order_sn,
                'send_order_id' => $send_order->id,
                'service_price' => $service_price,
                'total_price' => $total_price,
                'status' => 'unpaid'
            ]);
            //通知 发单人
            $message_data = [
                'task_type'=> 'send_order',
                'type' => 'extra_price_pay',
                'user_id' => $send_order->user_id,
                'total_price' => $total_price
            ];
            app(MessageService::class)->sendMessage($message_data);
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