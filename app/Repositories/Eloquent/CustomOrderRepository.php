<?php

namespace App\Repositories\Eloquent;

use App\Exceptions\OutputServerMessageException;
use App\Repositories\Eloquent\CustomOrderRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Services\RefundService;
use App\Services\MessageService;
use App\Models\User;
use App\Models\TaskOrder;
use App\Models\TaskOrderStatusChange;
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
            ->leftJoin('users as deliverers' ,'deliverers.id','custom_orders.deliverer_id')
            ->select(DB::raw('custom_orders.id,custom_orders.custom_order_category_id,custom_orders.order_sn,custom_orders.user_id,custom_orders.deliverer_id,custom_orders.coupon_name,custom_orders.coupon_price,custom_orders.total_price,custom_orders.tip,custom_orders.best_time,custom_orders.deliverer_price,custom_orders.order_price,custom_orders.order_status,custom_orders.order_cancel_status,custom_orders.postscript,custom_orders.payment,custom_orders.created_at,users.nickname,users.avatar_url,users.phone,deliverers.nickname as deliverer_nickname,deliverers.avatar_url as deliverer_avatar_url,deliverers.phone as deliverer_phone'))
            ->where('custom_orders.id',$id)
            ->first();

        $custom_order->friendly_date = friendly_date($custom_order->created_at);

        return $custom_order;
    }
    public function getAdminOrder($id)
    {
        $custom_order = $this->model->join('users','users.id','=','custom_orders.user_id')
            ->leftJoin('users as deliverers' ,'deliverers.id','custom_orders.deliverer_id')
            ->select(DB::raw('custom_orders.id,custom_orders.custom_order_category_id,custom_orders.order_sn,custom_orders.user_id,custom_orders.deliverer_id,custom_orders.coupon_name,custom_orders.coupon_price,custom_orders.tip,custom_orders.total_price,custom_orders.best_time,custom_orders.deliverer_price,custom_orders.order_price,custom_orders.order_status,custom_orders.order_cancel_status,custom_orders.postscript,custom_orders.payment,custom_orders.created_at,users.nickname,users.avatar_url,users.phone,deliverers.nickname as deliverer_nickname,deliverers.avatar_url as deliverer_avatar_url,deliverers.phone as deliverer_phone'))
            ->where('custom_orders.id',$id)
            ->first();

        $custom_order->friendly_date = friendly_date($custom_order->created_at);

        return $custom_order;
    }
    public function getOrderDetail($id)
    {
        $user = User::tokenAuth();
        $order = $this->find($id,['id','order_sn','custom_order_category_id','user_id','deliverer_id','tip','coupon_id','coupon_name','coupon_price','original_price','total_price','best_time','order_status','order_cancel_status','payment','deliverer_price','postscript','created_at']);
        $order->friendly_date = friendly_date($order->created_at);
        $order_data = $order->toArray();

        if(in_array($order->order_status,['unpaid']))
        {
            throw new OutputServerMessageException("???????????????");
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
    public function updateOrderStatus($data,$id)
    {
        $this->update($data,$id);
        $task_order_id = TaskOrder::where('type','custom_order')->where('objective_id',$id)->value('id');
        $task_data = [
            'order_status' => $data['order_status'],
        ];
        if(isset($data['order_cancel_status']))
        {
            $task_data['order_cancel_status'] = $data['order_cancel_status'];
        }
        app(TaskOrderRepository::class)->updateOrderStatus($data,$task_order_id);
    }
    public function acceptOrder($custom_order)
    {
        $deliverer = User::tokenAuthCache();

        if($custom_order->user_id == $deliverer->id)
        {
            throw new OutputServerMessageException("???????????????????????????");
        }
        if ($custom_order->order_status != 'new')
        {
            throw new OutputServerMessageException('???????????????');
        }
        if($custom_order->created_at < date('Y-m-d 00:00:00'))
        {
            throw new OutputServerMessageException('?????????????????????');
        }
//        if(date('H:i') > $custom_order->best_time)
//        {
//            throw new OutputServerMessageException('????????????????????????');
//        }
        try {
            $this->updateOrderStatus([
                'order_status' => 'accepted',
                'deliverer_id' => $deliverer->id,
            ],$custom_order->id);
            app(TaskOrderRepository::class)->where('type','custom_order')->where('objective_id',$custom_order->id)->updateData([
                'deliverer_id' => $deliverer->id
            ]);
            //???????????? ?????????
            $message_data = [
                'task_type'=> 'custom_order',
                'order_sn' => $custom_order->order_sn,
                'user_id' => $custom_order->user_id,
                'nickname' => $deliverer->nickname,
                'type' => 'accept_order',
            ];
            app(MessageService::class)->sendMessage($message_data);
        } catch (Exception $e) {
            throw new \App\Exceptions\OutputServerMessageException('??????????????????');
        }
        return $custom_order;
    }

    public function userCancelOrder($custom_order)
    {
        if ($custom_order->order_status == 'accepted') {
            throw new \App\Exceptions\OutputServerMessageException('??????????????????????????????????????????');
        }
        if ($custom_order->order_status != 'new') {
            throw new \App\Exceptions\OutputServerMessageException('?????????????????????????????????');
        }
        $data = [
            'id' => $custom_order->id,
            'total_price' => $custom_order->total_price,
            'order_sn' =>  $custom_order->order_sn,
            'payment' => $custom_order->payment,
            'coupon_id' => $custom_order->coupon_id,
            'coupon_price' => $custom_order->coupon_price,
            'trade_type' => 'CANCEL_CUSTOM_ORDER',
            'description' => '?????????????????????',
        ];
        app(RefundService::class)->refundHandle($data,'CustomOrder',User::tokenAuth());
    }
    public function finishOrder($custom_order)
    {
        if ($custom_order->order_status != 'accepted') {
            throw new \App\Exceptions\OutputServerMessageException('???????????????????????????????????????');
        }

        $this->updateOrderStatus(['order_status' => 'finish'],$custom_order->id);
        //?????? ?????????
        $message_data = [
            'task_type'=> 'custom_order',
            'order_sn' => $custom_order->order_sn,
            'user_id' => $custom_order->user_id,
            'type' => 'finish_order',
        ];
        app(MessageService::class)->sendMessage($message_data);
    }
    public function completeOrder($custom_order)
    {
        if ($custom_order->order_status != 'finish') {
            throw new \App\Exceptions\OutputServerMessageException('???????????????????????????????????????');
        }
        $deliverer = app(UserRepository::class)->where('id',$custom_order->deliverer_id)->first();
        $fee = get_fee($custom_order->deliverer_price);
        $income = $custom_order->deliverer_price - $fee;
        $new_balance = $deliverer->balance + $income;

        $balanceData = array(
            'user_id' => $deliverer->id,
            'balance' => $new_balance,
            'price'	=> $income,
            'out_trade_no' => $custom_order->order_sn,
            'fee' => $fee,
            'type' => 1,
            'trade_type' => 'ACCEPT_CUSTOM_ORDER',
            'description' => '??????????????????',
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
            'price' => $income,
            'fee' => $fee,
            'payment' => $custom_order->payment,
            'description' => '??????????????????',
        );

        $this->updateOrderStatus(['order_status' => 'completed','fee' => $fee],$custom_order->id);

        app(UserRepository::class)->update(['balance' => $new_balance],$deliverer->id);
        app(BalanceRecordRepository::class)->create($balanceData);

        app(TradeRecordRepository::class)->create($trade);
        //?????? ?????????
        $message_data = [
            'task_type'=> 'custom_order',
            'order_sn' => $custom_order->order_sn,
            'user_id' => $custom_order->deliverer_id,
            'type' => 'complete_order',
        ];
        app(MessageService::class)->sendMessage($message_data);
        return "success";
    }
    public function delivererCancelOrder($custom_order)
    {
        if ($custom_order->order_status != 'accepted') {
            throw new \App\Exceptions\OutputServerMessageException('?????????????????????????????????');
        }
        $this->updateOrderStatus(['order_status' => 'cancel','order_cancel_status' => 'deliverer_apply_cancel'],$custom_order->id);

        //?????? ?????????
        $message_data = [
            'task_type'=> 'custom_order',
            'order_sn' => $custom_order->order_sn,
            'user_id' => $custom_order->user_id,
            'type' => 'deliverer_cancel_order',
        ];
        app(MessageService::class)->sendMessage($message_data);
    }
    public function agreeCancelOrder($custom_order)
    {
        if ($custom_order->order_cancel_status != 'deliverer_apply_cancel') {
            throw new \App\Exceptions\OutputServerMessageException('???????????????????????????????????????');
        }
        $this->updateOrderStatus(['order_status' => 'cancel','order_cancel_status' => 'user_agree_cancel'],$custom_order->id);
        app(TaskOrderRepository::class)->where('type','custom_order')->where('objective_id',$custom_order->id)->updateData([
            'deliverer_id' => NULL
        ]);
        $this->updateOrderStatus(['deliverer_id' => NULL,'order_status' => 'new','order_cancel_status' => ''],$custom_order->id);
        /*
        $data = [
            'id' => $custom_order->id,
            'total_price' => $custom_order->total_price,
            'order_sn' =>  $custom_order->order_sn,
            'payment' => $custom_order->payment,
            'coupon_id' => $custom_order->coupon_id,
            'coupon_price' => $custom_order->coupon_price,
            'trade_type' => 'CANCEL_CUSTOM_ORDER',
            'description' => '?????????????????????',
        ];
        app(RefundService::class)->refundHandle($data,'CustomOrder',User::tokenAuth());
        */
        //?????? ?????????
        $message_data = [
            'task_type'=> 'custom_order',
            'order_sn' => $custom_order->order_sn,
            'user_id' => $custom_order->deliverer_id,
            'type' => 'user_agree_cancel_order',
        ];
        app(MessageService::class)->sendMessage($message_data);
    }
    public function disagreeCancelOrder($custom_order)
    {
        if ($custom_order->order_cancel_status != 'deliverer_apply_cancel') {
            throw new \App\Exceptions\OutputServerMessageException('????????????????????????????????????');
        }
        $this->updateOrderStatus(['order_status' => 'accepted','order_cancel_status' => 'user_disagree_cancel'],$custom_order->id);
        //?????? ?????????
        $message_data = [
            'task_type'=> 'custom_order',
            'order_sn' => $custom_order->order_sn,
            'user_id' => $custom_order->deliverer_id,
            'type' => 'user_disagree_cancel_order',
        ];
        app(MessageService::class)->sendMessage($message_data);
    }
}
