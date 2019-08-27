<?php

namespace App\Services;

use App\Exceptions\RequestSuccessException;
use App\Repositories\Eloquent\BalanceRecordRepositoryInterface;
use App\Repositories\Eloquent\CustomOrderRepositoryInterface;
use App\Repositories\Eloquent\TakeOrderExtraPriceRepositoryInterface;
use App\Repositories\Eloquent\TakeOrderRepositoryInterface;
use App\Repositories\Eloquent\TradeRecordRepositoryInterface;
use App\Repositories\Eloquent\UserCouponRepositoryInterface;
use App\Repositories\Eloquent\UserRepositoryInterface;
use App\Repositories\Eloquent\TaskOrderRepositoryInterface;
use Validator,Request,DB,Log;
use App\Models\Setting;
use App\Models\User;
//use Illuminate\Http\Request;
use EasyWeChat\Factory;


class RefundService
{
    public $user;

    public function __construct(TakeOrderRepositoryInterface $takeOrderRepository,
                                UserRepositoryInterface $userRepository,
                                BalanceRecordRepositoryInterface $balanceRecordRepository,
                                TradeRecordRepositoryInterface $tradeRecordRepository,
                                TaskOrderRepositoryInterface $taskOrderRepository,
                                TakeOrderExtraPriceRepositoryInterface $takeOrderExtraPriceRepository,
                                CustomOrderRepositoryInterface $customOrderRepository,
                                UserCouponRepositoryInterface $userCouponRepository)
    {
        $this->takeOrderRepository = $takeOrderRepository;
        $this->userRepository = $userRepository;
        $this->balanceRecordRepository = $balanceRecordRepository;
        $this->tradeRecordRepository = $tradeRecordRepository;
        $this->taskOrderRepository = $taskOrderRepository;
        $this->takeOrderExtraPriceRepository = $takeOrderExtraPriceRepository;
        $this->customOrderRepository = $customOrderRepository;
        $this->userCouponRepository = $userCouponRepository;
    }

    public function refundHandle($data,$pay_from)
    {
        $this->user = User::tokenAuth();
        switch($pay_from)
        {
            case 'TakeOrder':
                return $this->takeOrderRefundHandle($data);
                break;
            case 'CustomOrder':
                return $this->customOrderRefundHandle($data);
            default :
                throw new \App\Exceptions\OutputServerMessageException('操作失败');
                break;
        }

    }
    public function takeOrderRefundHandle($data)
    {
        if($data['payment'] == 'balance')
        {
            $result = $this->balance($data);
            if($result['return_code'] == 'SUCCESS')
            {
                $this->takeOrderRepository->updateOrderStatus(['order_status' => 'cancel','order_cancel_status' => 'refunded'],$data['id']);
                throw new \App\Exceptions\RequestSuccessException("取消任务成功，任务费用已原路退回，请注意查收!");
            }

        }else{
            //TODO:在线支付退款
            exit;
            $trade = array(
                'type' => 1,
                'trade_type' => 'CANCEL_TAKE_ORDER',
                'trade_status' => 'refunding',
                'description' => '取消代拿任务',
            );
            $this->tradeRecordRepository->where('out_trade_no',$data['order_sn'])->updateData($trade);
            $this->takeOrderRepository->updateOrderStatus(['order_status' => 'cancel'],$data['id']);

            throw new \App\Exceptions\RequestSuccessException("取消任务成功，任务费用已原路退回，请注意查收");
        }
    }
    public function customOrderRefundHandle($data)
    {
        if($data['payment'] == 'balance')
        {
            $result = $this->balance($data);
            if($result['return_code'] == 'SUCCESS')
            {
                $this->customOrderRepository->updateOrderStatus(['order_status' => 'cancel','order_cancel_status' => 'refunded'],$data['id']);
                throw new \App\Exceptions\RequestSuccessException("取消任务成功，任务费用已原路退回，请注意查收!");
            }

        }else{
            //TODO:在线支付退款
            exit;

        }
    }
    private function balance($data)
    {
        $new_balance = $this->user->balance + $data['total_price'];
        $balanceData = array(
            'user_id' => $this->user->id,
            'balance' => $new_balance,
            'price'	=> $data['total_price'],
            'out_trade_no' => $data['order_sn'],
            'fee' => 0,
            'type' => 1,
            'trade_type' => $data['trade_type'],
            'description' => $data['description'],
        );
        $this->balanceRecordRepository->create($balanceData);
        $this->userRepository->update(['balance' => $new_balance],$this->user->id);

        $trade = array(
            'trade_status' => 'refunded',
            'type' => 1,
            'trade_type' => $data['trade_type'],
            'description' => $data['description'],
        );
        $this->tradeRecordRepository->where('out_trade_no',$data['order_sn'])->updateData($trade);
        return [
            'return_code' => 'SUCCESS',
        ];
    }
}
