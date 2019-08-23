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
use EasyWeChat\Factory;


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
            case 'TakeOrderExtraPrice':
                return $this->takeOrderExtraPricePayHandle($data);
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
                $pay_config =  $this->wechat($parameter);
                return [
                    'task_order_id' => $data['task_order_id'],
                    'take_order_id' => $data['take_order_id'],
                    'order_sn' => $data['order_sn'],
                    'pay_config' => $pay_config,
                ];
                break;
            case 'balance':

                $result = $this->balance($data);
                if($result['return_code'] == 'SUCCESS')
                {
                    $this->takeOrderRepository->updateOrderStatus(['order_status' => 'new'],$data['take_order_id']);
                    $data['user_coupon_id'] ? $this->userCouponRepository->update(['status' => 'used'],$data['user_coupon_id']) : '';
                    return [
                        'task_order_id' => $data['task_order_id'],
                        'take_order_id' => $data['take_order_id'],
                        'order_sn' => $data['order_sn'],
                    ];
                }
                break;
        }
    }
    private function takeOrderExtraPricePayHandle($data)
    {
        switch($data['payment']) {
            //微信
            case 'wechat':
                $parameter = [
                    'body'             => $data['body'],
                    'detail'           => $data['detail'],
                    'out_trade_no'     => $data['order_sn'],
                    'total_price'      => $data['total_price'] * 100, // 单位：分
                    'notify_url'       => config('common.wechat_notify_url'),
                ];
                $pay_config =  $this->wechat($parameter);
                return [
                    'pay_config' => $pay_config,
                ];
                break;
            case "balance":
                $data = $this->balance($data);
                break;
        }
    }
    private function wechat($parameter)
    {
        $config = [
            'app_id' => config('wechat.mini_program.default.app_id'),
            'mch_id' => config('wechat.payment.default.mch_id'),
            'key' => config('wechat.payment.default.key'),
        ];
        $app = Factory::payment($config);
        $jssdk = $app->jssdk;
        $result = $app->order->unify([
            'body' => $parameter['body'],
            'out_trade_no' => $parameter['out_trade_no'],
            'total_fee' => $parameter['total_price'],
            'notify_url' => $parameter['notify_url'], // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'trade_type' => 'JSAPI', // 请对应换成你的支付方式对应的值类型
            'openid' => $this->user->open_id,
        ]);

        if ($result['return_code'] == 'SUCCESS'){
            $prepayId = $result['prepay_id'];
            $pay_config = $jssdk->bridgeConfig($prepayId, false);
        }else{
            throw new \App\Exceptions\OutputServerMessageException('未知错误,请使用其他支付方式！');
        }
        return $pay_config;
    }
    private function balance($data)
    {
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
            $trade_no = 'BALANCE-'.$data['order_sn'];
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
            return [
                'return_code' => 'SUCCESS',
            ];
        }else{
            throw new \App\Exceptions\OutputServerMessageException('支付失败');
        }
    }
    public function paySuccess()
    {

    }
}
