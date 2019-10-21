<?php

namespace App\Repositories\Eloquent;

use App\Exceptions\OutputServerMessageException;
use App\Repositories\Eloquent\SendOrderRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Models\User;
use App\Models\TaskOrder;
use App\Models\TaskOrderStatusChange;
use App\Services\MessageService;
use App\Services\RefundService;
use Request,DB;

class SendOrderRepository extends BaseRepository implements SendOrderRepositoryInterface
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
        return config('model.send_order.send_order.model');
    }
    /*
     * TODO:created_at 今天
     */
    public function getOrders($where=[])
    {
        $limit = Request::get('limit',config('app.limit'));
        $send_orders = $this->model->join('users','users.id','=','send_orders.user_id')
            ->select(DB::raw('send_orders.id,send_orders.order_sn,send_orders.user_id,send_orders.deliverer_id,send_orders.coupon_id,send_orders.coupon_name,send_orders.coupon_price,send_orders.item_type_name,send_orders.express_company_name,send_orders.best_time,send_orders.order_status,send_orders.order_cancel_status,send_orders.payment,send_orders.urgent,send_orders.urgent_price,send_orders.original_price,send_orders.order_price,send_orders.total_price,send_orders.deliverer_price,send_orders.fee,send_orders.postscript,send_orders.sender,send_orders.sender_mobile,send_orders.sender_address,send_orders.consignee,send_orders.consignee_mobile,send_orders.consignee_address,send_orders.created_at,CASE send_orders.order_status WHEN "new" THEN 1 ELSE 2 END as status_num,users.nickname,users.avatar_url'))
            ->whereIn('send_orders.order_status', ['new','accepted']);
        if($where)
        {
            $send_orders->where($where);
        }
            //->where('send_orders.created_at','>',date("Y-m-d 00:00:00"))
        $send_orders = $send_orders->orderBy('status_num','asc')
            ->orderBy('send_orders.id','desc')
            ->paginate($limit);

        foreach ($send_orders as $key => $send_order)
        {
            $send_order->friendly_date = friendly_date($send_order->created_at);
        }
        return $send_orders;
    }
    public function getOrder($id)
    {
        $send_order = $this->model->join('users','users.id','=','send_orders.user_id')
            ->select(DB::raw('send_orders.id,send_orders.order_sn,send_orders.user_id,send_orders.deliverer_id,send_orders.coupon_id,send_orders.coupon_name,send_orders.coupon_price,send_orders.item_type_name,send_orders.express_company_name,send_orders.best_time,send_orders.order_status,send_orders.order_cancel_status,send_orders.payment,send_orders.urgent,send_orders.urgent_price,send_orders.original_price,send_orders.order_price,send_orders.total_price,send_orders.deliverer_price,send_orders.fee,send_orders.postscript,send_orders.sender,send_orders.sender_mobile,send_orders.sender_address,send_orders.consignee,send_orders.consignee_mobile,send_orders.consignee_address,send_orders.created_at,users.nickname,users.avatar_url'))
            ->where('send_orders.id',$id)
            ->first();
        $send_order->friendly_date = friendly_date($send_order->created_at);
        return $send_order;
    }
    public function getAdminOrder($id)
    {
        $send_order = $this->model->join('users','users.id','=','send_orders.user_id')
            ->leftJoin('users as deliverers' ,'deliverers.id','send_orders.deliverer_id')
            ->select(DB::raw('send_orders.id,send_orders.order_sn,send_orders.user_id,send_orders.deliverer_id,send_orders.coupon_id,send_orders.coupon_name,send_orders.coupon_price,send_orders.item_type_name,send_orders.express_company_name,send_orders.best_time,send_orders.order_status,send_orders.order_cancel_status,send_orders.payment,send_orders.urgent,send_orders.urgent_price,send_orders.original_price,send_orders.order_price,send_orders.total_price,send_orders.deliverer_price,send_orders.fee,send_orders.postscript,send_orders.sender,send_orders.sender_mobile,send_orders.sender_address,send_orders.consignee,send_orders.consignee_mobile,send_orders.consignee_address,send_orders.created_at,users.nickname,users.avatar_url,users.phone,deliverers.nickname as deliverer_nickname,deliverers.avatar_url as deliverer_avatar_url,deliverers.phone as deliverer_phone'))
            ->where('send_orders.id',$id)
            ->first();
        $send_order->friendly_date = friendly_date($send_order->created_at);
        return $send_order;
    }
    public function getOrderDetail($id)
    {
        $user = User::tokenAuth();
        $send_order = $this->find($id,['id','order_sn','user_id','deliverer_id','coupon_id','coupon_name','coupon_price','item_type_name','express_company_name','best_time','order_status','order_cancel_status','payment','urgent','urgent_price','original_price','order_price','total_price','deliverer_price','fee','postscript','sender','sender_mobile','sender_address','consignee','consignee_mobile','consignee_address','created_at']);
        $send_order->friendly_date = friendly_date($send_order->created_at);
        $send_order_data = $send_order->toArray();

        if(in_array($send_order->order_status,['unpaid']))
        {
            throw new OutputServerMessageException("该订单无效");
        }

        $send_order_user = app(UserRepository::class)->find($send_order->user_id);
        $send_order_deliverer = app(UserRepository::class)->where('id',$send_order->deliverer_id)->first();

        $user_field = ['id','avatar_url','nickname'];

        if($send_order->deliverer_id == $user->id || $send_order->user_id == $user->id)
        {
            $user_field = array_merge($user_field,['phone']);

        }

        $send_order_user_data = visible_data($send_order_user->toArray(),$user_field);
        $send_order_deliverer_data = $send_order_deliverer ? visible_data($send_order_deliverer->toArray(),$user_field) : [];
        $data = [
            'send_order' => $send_order_data,
            'user' => $send_order_user_data,
            'deliverer' => $send_order_deliverer_data
        ];
        return $data;
    }
    public function acceptOrder($send_order)
    {
        $deliverer = User::tokenAuthCache();
        //检验接单人跟发单人是否为同一人
        if($send_order->user_id == $deliverer->id)
        {
            throw new OutputServerMessageException("不能接自己发的任务");
        }
        if ($send_order->order_status != 'new')
        {
            throw new \App\Exceptions\OutputServerMessageException('任务已被接');
        }
        if($send_order->created_at < date('Y-m-d 00:00:00'))
        {
            throw new \App\Exceptions\OutputServerMessageException('任务已过有效期');
        }
        try {
            $this->updateOrderStatus([
                'order_status' => 'accepted',
                'deliverer_id' => $deliverer->id,
            ],$send_order->id);
            app(TaskOrderRepository::class)->where('type','send_order')->where('objective_id',$send_order->id)->updateData([
                'deliverer_id' => $deliverer->id
            ]);
            //消息推送 发单人
            $message_data = [
                'task_type'=> 'send_order',
                'order_sn' => $send_order->order_sn,
                'user_id' => $send_order->user_id,
                'nickname' => $deliverer->nickname,
                'type' => 'accept_order',
            ];
            app(MessageService::class)->sendMessage($message_data);
        } catch (Exception $e) {
            throw new \App\Exceptions\OutputServerMessageException('无法接受任务');
        }
        return $send_order;

    }
    public function updateOrderStatus($data,$id)
    {
        $this->update($data,$id);
        $task_order_id = TaskOrder::where('type','send_order')->where('objective_id',$id)->value('id');
        $task_data = [
            'order_status' => $data['order_status'],
        ];
        if(isset($data['order_cancel_status']))
        {
            $task_data['order_cancel_status'] = $data['order_cancel_status'];
        }
        app(TaskOrderRepository::class)->updateOrderStatus($task_data,$task_order_id);
    }
    public function finishOrder($send_order)
    {
        if ($send_order->order_status != 'paid_carriage') {
            throw new \App\Exceptions\OutputServerMessageException('当前任务状态不允许完成任务');
        }
        $this->updateOrderStatus(['order_status' => 'finish'],$send_order->id);

        //通知 发单人
        $message_data = [
            'task_type'=> 'send_order',
            'order_sn' => $send_order->order_sn,
            'user_id' => $send_order->user_id,
            'type' => 'finish_order',
        ];
        app(MessageService::class)->sendMessage($message_data);
        return "success";
    }
    public function completeOrder($send_order)
    {
        if ($send_order->order_status != 'finish') {
            throw new \App\Exceptions\OutputServerMessageException('当前任务状态不允许结算任务');
        }

        $deliverer = app(UserRepository::class)->where('id',$send_order->deliverer_id)->first();
        $fee = get_fee($send_order->deliverer_price);
        $income = $send_order->deliverer_price - $fee;
        $new_balance = $deliverer->balance + $income;
        $balanceData = array(
            'user_id' => $deliverer->id,
            'balance' => $new_balance,
            'price'	=> $income,
            'out_trade_no' => $send_order->order_sn,
            'fee' => $fee,
            'type' => 1,
            'trade_type' => 'ACCEPT_SEND_ORDER',
            'description' => '接代寄任务',
        );

        $trade_no = 'BALANCE-'.generate_order_sn();
        $trade = array(
            'user_id' => $deliverer->id,
            'out_trade_no' => $send_order->order_sn,
            'trade_no' => $trade_no,
            'trade_status' => 'income',
            'type' => 1,
            'pay_from' => 'SendOrder',
            'trade_type' => 'ACCEPT_SEND_ORDER',
            'price' => $income,
            'fee' => $fee,
            'payment' => $send_order->payment,
            'description' => '接代寄任务',
        );

        $this->updateOrderStatus(['order_status' => 'completed','fee' => $fee],$send_order->id);

        app(UserRepository::class)->update(['balance' => $new_balance],$deliverer->id);
        app(BalanceRecordRepository::class)->create($balanceData);

        app(TradeRecordRepository::class)->create($trade);

        //通知 接单人
        $message_data = [
            'task_type'=> 'send_order',
            'order_sn' => $send_order->order_sn,
            'user_id' => $send_order->deliverer_id,
            'type' => 'complete_order',
        ];
        app(MessageService::class)->sendMessage($message_data);

        return "success";
    }
    public function userCancelOrder($send_order)
    {
        if ($send_order->order_status == 'accepted') {
            throw new \App\Exceptions\OutputServerMessageException('已被接单，请联系骑手取消任务');
        }
        if ($send_order->order_status != 'new') {
            throw new \App\Exceptions\OutputServerMessageException('当前任务状态不允许取消');
        }
        $data = [
            'id' => $send_order->id,
            'total_price' => $send_order->total_price,
            'order_sn' => $send_order->order_sn,
            'payment' => $send_order->payment,
            'coupon_id' => $send_order->coupon_id,
            'coupon_price' => $send_order->coupon_price,
            'trade_type' => 'CANCEL_SEND_ORDER',
            'description' => '取消代寄任务',
        ];
        app(RefundService::class)->refundHandle($data,'SendOrder',User::tokenAuth());
    }
    public function delivererCancelOrder($send_order)
    {
        if ($send_order->order_status != 'accepted') {
            throw new \App\Exceptions\OutputServerMessageException('当前任务状态不允许取消');
        }
        $this->updateOrderStatus(['order_status' => 'cancel','order_cancel_status' => 'deliverer_apply_cancel'],$send_order->id);

        //通知 发单人
        $message_data = [
            'task_type'=> 'send_order',
            'order_sn' => $send_order->order_sn,
            'user_id' => $send_order->user_id,
            'type' => 'deliverer_cancel_order',
        ];
        app(MessageService::class)->sendMessage($message_data);

    }
    public function agreeCancelOrder($send_order)
    {
        if ($send_order->order_cancel_status != 'deliverer_apply_cancel') {
            throw new \App\Exceptions\OutputServerMessageException('当前任务状态不允许同意取消');
        }
        $this->updateOrderStatus(['order_status' => 'cancel','order_cancel_status' => 'user_agree_cancel'],$send_order->id);

        app(TaskOrderRepository::class)->where('type','send_order')->where('objective_id',$send_order->id)->updateData([
            'deliverer_id' => NULL
        ]);
        $this->updateOrderStatus(['deliverer_id' => NULL,'order_status' => 'new','order_cancel_status' => ''],$send_order->id);
        /*
        $data = [
            'id' => $send_order->id,
            'total_price' => $send_order->total_price,
            'order_sn' =>  $send_order->order_sn,
            'payment' => $send_order->payment,
            'coupon_id' => $send_order->coupon_id,
            'coupon_price' => $send_order->coupon_price,
            'trade_type' => 'CANCEL_SEND_ORDER',
            'description' => '取消代寄任务',
        ];
        app(RefundService::class)->refundHandle($data,'SendOrder',User::tokenAuth());
         */
        //通知 接单人
        $message_data = [
            'task_type'=> 'send_order',
            'order_sn' => $send_order->order_sn,
            'user_id' => $send_order->deliverer_id,
            'type' => 'user_agree_cancel_order',
        ];
        app(MessageService::class)->sendMessage($message_data);

    }
    public function disagreeCancelOrder($send_order)
    {
        if ($send_order->order_cancel_status != 'deliverer_apply_cancel') {
            throw new \App\Exceptions\OutputServerMessageException('当前任务状态不允许该操作');
        }
        $this->updateOrderStatus(['order_status' => 'accepted','order_cancel_status' => 'user_disagree_cancel'],$send_order->id);
        //通知 接单人
        $message_data = [
            'task_type'=> 'send_order',
            'order_sn' => $send_order->order_sn,
            'user_id' => $send_order->deliverer_id,
            'type' => 'user_disagree_cancel_order',
        ];
        app(MessageService::class)->sendMessage($message_data);
    }

}
