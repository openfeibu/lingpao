<?php

namespace App\Repositories\Eloquent;

use App\Exceptions\OutputServerMessageException;
use App\Repositories\Eloquent\CustomOrderRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Services\RefundService;
use App\Models\User;
use Request,DB;

class CustomOrderRepository extends BaseRepository implements CustomOrderRepositoryInterface
{

    /**
     * Booting the repository.
     *
     * @return null
     */
    /*
    public function boot()
    {
        $this->fieldSearchable = config('model.user.user_address.search');
    }
    */

    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return config('model.custom_order.custom_order.model');
    }
    public function getOrder($id)
    {
        $custom_order = $this->model->join('users','users.id','=','custom_orders.user_id')
            ->select(DB::raw('custom_orders.id,custom_orders.custom_order_category_id,custom_orders.order_sn,custom_orders.user_id,custom_orders.deliverer_id,custom_orders.total_price,custom_orders.best_time,custom_orders.deliverer_price,custom_orders.order_status,custom_orders.order_cancel_status,custom_orders.postscript,custom_orders.created_at,users.nickname,users.avatar_url'))
            ->where('custom_orders.id',$id)
            ->first();

        $custom_order->friendly_date = friendly_date($custom_order->created_at);

        return $custom_order;
    }
    public function updateOrderStatus($data,$id)
    {
        $this->update($data,$id);
        app(TaskOrderRepository::class)->where('type','custom_order')->where('objective_id',$id)->updateData([
            'order_status' => $data['order_status']
        ]);
    }
    public function getOrderDetail($id)
    {
        $user = User::tokenAuth();
        $order = $this->find($id,['id','order_sn','custom_order_category_id','user_id','deliverer_id','tip','coupon_id','coupon_name','coupon_price','original_price','total_price','best_time','order_status','order_cancel_status','payment','deliverer_price','postscript','created_at']);
        $order->friendly_date = friendly_date($order->created_at);
        $order_data = $order->toArray();

        if(in_array($order->order_status,['unpaid']))
        {
            throw OutputServerMessageException("该订单无效");
        }

        $order_user = app(UserRepository::class)->find($order->user_id);
        $order_deliverer = app(UserRepository::class)->where('id',$order->deliverer_id)->first();

        $user_field = ['id','avatar_url','nickname'];

        if($order->deliverer_id == $user->id || $order->user_id == $user->id)
        {
            $user_field = array_merge($user_field,['phone']);
        }

        $order_user_data = visible_data($order_user->toArray(),$user_field);
        $order_deliverer_data = $order_deliverer ? visible_data($order_deliverer->toArray(),$user_field) : [];
        $data = [
            'custom_order' => $order_data,
            'user' => $order_user_data,
            'deliverer' => $order_deliverer_data
        ];
        return $data;
    }
    public function acceptOrder($custom_order)
    {
        $deliverer = User::tokenAuthCache();

        if($custom_order->user_id == $deliverer->id)
        {
            throw new OutputServerMessageException("不能接自己发的任务");
        }
        if ($custom_order->order_status != 'new')
        {
            throw new OutputServerMessageException('任务已被接');
        }
        if($custom_order->created_at < date('Y-m-d 00:00:00'))
        {
            throw new OutputServerMessageException('任务已过有效期');
        }
        if(date('H:i') > $custom_order->best_time)
        {
            throw new OutputServerMessageException('任务已过期待时间');
        }
        try {
            $this->updateOrderStatus([
                'order_status' => 'accepted',
                'deliverer_id' => $deliverer->id,
            ],$custom_order->id);
            app(TaskOrderRepository::class)->where('type','custom_order')->where('objective_id',$custom_order->id)->updateData([
                'deliverer_id' => $deliverer->id
            ]);
            //TODO:消息推送

        } catch (Exception $e) {
            throw new \App\Exceptions\RequestFailedException('无法接受任务');
        }
        return $custom_order;
    }

    public function delivererCancelOrder($custom_order)
    {
        if ($custom_order->order_status != 'accepted') {
            throw new \App\Exceptions\OutputServerMessageException('当前任务状态不允许取消');
        }
        $this->updateOrderStatus(['order_status' => 'cancel','order_cancel_status' => 'deliverer_apply_cancel'],$custom_order->id);

        throw new \App\Exceptions\RequestSuccessException("操作成功，请等待或联系用户确认！");
    }
    public function agreeCancelOrder($custom_order)
    {
        if ($custom_order->order_cancel_status != 'deliverer_apply_cancel') {
            throw new \App\Exceptions\OutputServerMessageException('当前任务状态不允许同意取消');
        }
        $this->updateOrderStatus(['order_status' => 'cancel','order_cancel_status' => 'user_agree_cancel'],$custom_order->id);
        $data = [
            'id' => $custom_order->id,
            'total_price' => $custom_order->total_price,
            'order_sn' =>  $custom_order->order_sn,
            'payment' => $custom_order->payment,
            'trade_type' => 'CANCEL_CUSTOM_ORDER',
            'description' => '取消帮帮忙任务',
        ];
        app(RefundService::class)->refundHandle($data,'CustomOrder');
    }
    public function userCancelOrder($custom_order)
    {
        if ($custom_order->order_status == 'accepted') {
            throw new \App\Exceptions\OutputServerMessageException('已被接单，请联系骑手取消任务');
        }
        if ($custom_order->order_status != 'new') {
            throw new \App\Exceptions\OutputServerMessageException('当前任务状态不允许取消');
        }
        $data = [
            'id' => $custom_order->id,
            'total_price' => $custom_order->total_price,
            'order_sn' =>  $custom_order->order_sn,
            'payment' => $custom_order->payment,
            'trade_type' => 'CANCEL_CUSTOM_ORDER',
            'description' => '取消帮帮忙任务',
        ];
        app(RefundService::class)->refundHandle($data,'CustomOrder');
    }
    public function completeOrder($custom_order)
    {
        if ($custom_order->order_status != 'finish') {
            throw new \App\Exceptions\OutputServerMessageException('当前任务状态不允许结算任务');
        }

        $deliverer = app(UserRepository::class)->where('id',$custom_order->deliverer_id)->first();
        $new_balance = $deliverer->balance + $custom_order->deliverer_price;
        $balanceData = array(
            'user_id' => $deliverer->id,
            'balance' => $new_balance,
            'price'	=> $custom_order->deliverer_price,
            'out_trade_no' => $custom_order->order_sn,
            'fee' => 0,
            'type' => 1,
            'trade_type' => 'ACCEPT_CUSTOM_ORDER',
            'description' => '接帮帮忙任务',
        );

        $trade_no = 'BALANCE-'.generate_order_sn();
        $trade = array(
            'user_id' => $deliverer->id,
            'out_trade_no' => $custom_order->order_sn,
            'trade_no' => $trade_no,
            'trade_status' => 'income',
            'type' => 1,
            'pay_from' => 'CustomOrder',
            'trade_type' => 'ACCEPT_CUSTOM_ORDER',
            'price' => $custom_order->deliverer_price,
            'payment' => $custom_order->payment,
            'description' => '接帮帮忙任务',
        );

        $this->updateOrderStatus(['order_status' => 'completed'],$custom_order->id);

        app(UserRepository::class)->update(['balance' => $new_balance],$deliverer->id);
        app(BalanceRecordRepository::class)->create($balanceData);

        app(TradeRecordRepository::class)->create($trade);

        return "success";
    }
}
