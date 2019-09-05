<?php

namespace App\Http\Controllers\Api\Deliverer;

use App\Exceptions\OutputServerMessageException;
use App\Exceptions\PermissionDeniedException;
use App\Exceptions\RequestSuccessException;
use App\Repositories\Eloquent\UserRepositoryInterface;
use App\Repositories\Eloquent\CustomOrderRepositoryInterface;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Api\BaseController;
use Log;

class CustomOrderController extends BaseController
{
    public function __construct(CustomOrderRepositoryInterface $customOrderRepository,
                                UserRepositoryInterface $userRepository)
    {
        parent::__construct();
        $this->middleware('auth.api',['except' => []]);
        $this->customOrderRepository = $customOrderRepository;
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

            $custom_order = $this->customOrderRepository->find($request->id);
            //接受任务
            $this->customOrderRepository->acceptOrder($custom_order);

            return $this->response->success("恭喜，接单成功")->data(['task_order_id' => $custom_order->task_order_id,'custom_order_id' => $custom_order->id])->json();
        }
        else {
            throw new OutputServerMessageException('接单失败，系统繁忙！');
        }
        @fclose($fp);
    }

    /**
     * 接单人完成任务
     */
    public function finishOrder(Request $request)
    {

        $rule = [
            'id' => 'required|integer',
        ];
        validateParameter($rule);

        $custom_order = $this->customOrderRepository->find($request->id);

        $this->checkDelivererPermission($custom_order->deliverer_id);


        $this->customOrderRepository->finishOrder($custom_order);

        throw new \App\Exceptions\RequestSuccessException("恭喜，已完成任务！等待用户确认！");
    }
    public function cancelOrder(Request $request)
    {
        $rule = [
            'id' => 'required|integer',
        ];
        validateParameter($rule);

        $custom_order = $this->customOrderRepository->find($request->id);

        $this->checkDelivererPermission($custom_order->deliverer_id);

        $this->customOrderRepository->delivererCancelOrder($custom_order);

        throw new \App\Exceptions\RequestSuccessException();
    }
    public function submitServicePrice(Request $request)
    {

    }
    private function checkDelivererPermission($deliverer_id,$message="")
    {
        if($deliverer_id != $this->deliverer->id){
            throw new PermissionDeniedException($message);
        }
    }
}