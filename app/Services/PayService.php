<?php

namespace App\Services;

use App\Repositories\Eloquent\BalanceRecordRepositoryInterface;
use App\Repositories\Eloquent\TakeOrderRepositoryInterface;
use App\Repositories\Eloquent\TradeRecordRepositoryInterface;
use App\Repositories\Eloquent\UserCouponRepositoryInterface;
use App\Repositories\Eloquent\UserRepositoryInterface;
use App\Repositories\Eloquent\TaskOrderRepositoryInterface;
use Validator,Request,DB,Log;
use App\Models\Setting;
use App\Models\User;
//use Illuminate\Http\Request;

class PayService
{
    public $user;

    public function __construct(TakeOrderRepositoryInterface $takeOrderRepository,
                                UserRepositoryInterface $userRepository,
                                BalanceRecordRepositoryInterface $balanceRecordRepository,
                                TradeRecordRepositoryInterface $tradeRecordRepository,
                                TaskOrderRepositoryInterface $taskOrderRepository,
                                UserCouponRepositoryInterface $userCouponRepository)
    {
        $this->takeOrderRepository = $takeOrderRepository;
        $this->userRepository = $userRepository;
        $this->balanceRecordRepository = $balanceRecordRepository;
        $this->tradeRecordRepository = $tradeRecordRepository;
        $this->taskOrderRepository = $taskOrderRepository;
        $this->userCouponRepository = $userCouponRepository;
    }

    public function payHandle($data)
    {
        $this->user = User::tokenAuth();
        switch($data['pay_from'])
        {
            case 'TakeOrder':
                return $this->takeOrderPayHandle($data);
                break;
            default :
                throw new \App\Exceptions\OutputServerMessageException('操作失败');
                break;
        }

    }
    private function takeOrderPayHandle($data)
    {
        switch($data['payment'])
        {
            //微信
            case 'wechat':
                $parameter = [
                    'body'             => $data['body'],
                    'detail'           => $data['detail'],
                    'out_trade_no'     => $data['order_sn'],
                    'total_price'      => $data['total_price'] * 100, // 单位：分
                    'notify_url'       => config('common.wechat_notify_url'),
                ];
                return $this->wechat($data,$parameter);
                break;
            case 'balance':
                $new_balance = $this->user->balance - $data['total_price'];
                $update_balance = $this->userRepository->update(['balance' => $new_balance],$this->user->id);
                if($update_balance){
                    $balanceData = array(
                        'user_id' => $this->user->id,
                        'balance' => $new_balance,
                        'price'	=> $data['total_price'],
                        'out_trade_no' => $data['order_sn'],
                        'type' => -1,
                        'trade_type' => $data['trade_type'],
                        'description' => $data['detail'],
                    );
                    $this->balanceRecordRepository->create($balanceData);
                    $trade_no = 'balance'.$data['order_sn'];
                    $trade = array(
                        'user_id' => $this->user->id,
                        'out_trade_no' => $data['order_sn'],
                        'trade_no' => $trade_no,
                        'trade_status' => 'success',
                        'type' => -1,
                        'pay_from' => $data['pay_from'],
                        'trade_type' => $data['trade_type'],
                        'price' => $data['total_price'],
                        'payment' => $data['payment'],
                        'description' => $data['detail'],
                    );
                    $this->tradeRecordRepository->create($trade);
                    $this->takeOrderRepository->where('order_sn',$data['order_sn'])->updateData(['order_status' => 'new']);
                    $data['user_coupon_id'] ? $this->userCouponRepository->update(['status' => 'used'],$data['user_coupon_id']) : '';

                    $task_order = $this->taskOrderRepository->where('type','take_order')->where('objective_id',$data['take_order_id'])->updateData(['order_status' => 'new']);
                    return [
                        'task_order_id' => $data['task_order_id'],
                        'take_order_id' => $data['take_order_id'],
                        'order_sn' => $data['order_sn'],
                    ];
                }else{
                    throw new \App\Exceptions\OutputServerMessageException('支付失败');
                }
                break;
        }
    }
    private function wechat($data,$parameter)
    {

    }
}
