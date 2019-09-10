<?php

namespace App\Repositories\Eloquent;

use App\Exceptions\OutputServerMessageException;
use App\Repositories\Eloquent\TakeOrderRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Models\TaskOrderStatusChange;
use App\Models\User;
use App\Services\MessageService;
use App\Services\RefundService;
use Request,DB;

class TakeOrderRepository extends BaseRepository implements TakeOrderRepositoryInterface
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
        return config('model.take_order.take_order.model');
    }
    /*
     * TODO:created_at 今天
     */
    public function getOrders($where=[])
    {
        $limit = Request::get('limit',config('app.limit'));
        $take_orders = $this->model->join('users','users.id','=','take_orders.user_id')
            ->select(DB::raw('take_orders.id,take_orders.order_sn,take_orders.user_id,take_orders.deliverer_id,take_orders.urgent,take_orders.total_price,take_orders.deliverer_price,take_orders.express_count,take_orders.order_status,take_orders.order_cancel_status,take_orders.postscript,take_orders.payment,take_orders.created_at,CASE take_orders.order_status WHEN "new" THEN 1 ELSE 2 END as status_num,users.nickname,users.avatar_url'))
            ->whereIn('take_orders.order_status', ['new','accepted']);
        if($where)
        {
            $take_orders->where($where);
        }
            //->where('take_orders.created_at','>',date("Y-m-d 00:00:00"))
        $take_orders = $take_orders->orderBy('status_num','asc')
            ->orderBy('take_orders.id','desc')
            ->paginate($limit);

        foreach ($take_orders as $key => $take_order)
        {
            $take_order->friendly_date = friendly_date($take_order->created_at);
            $take_order->expresses = app(TakeOrderExpressRepository::class)->where('take_order_id',$take_order->id)
                ->orderBy('id','asc')->get(['take_place','address']);
        }
        return $take_orders;
    }
    public function getOrder($id)
    {
        $take_order = $this->model->join('users','users.id','=','take_orders.user_id')
            ->select(DB::raw('take_orders.id,take_orders.order_sn,take_orders.user_id,take_orders.deliverer_id,take_orders.urgent,take_orders.total_price,take_orders.deliverer_price,express_count,take_orders.order_status,take_orders.order_cancel_status,take_orders.postscript,take_orders.payment,take_orders.created_at,users.nickname,users.avatar_url'))
            ->where('take_orders.id',$id)
            ->first();
        $take_order->friendly_date = friendly_date($take_order->created_at);
        $take_order->expresses = app(TakeOrderExpressRepository::class)->getExpresses($take_order->id);
        return $take_order;
    }
    public function getAdminOrder($id)
    {
        $take_order = $this->model->join('users','users.id','=','take_orders.user_id')
            ->leftJoin('users as deliverers' ,'deliverers.id','take_orders.deliverer_id')
            ->select(DB::raw('take_orders.id,take_orders.order_sn,take_orders.user_id,take_orders.deliverer_id,take_orders.urgent,take_orders.coupon_name,take_orders.coupon_price,take_orders.total_price,take_orders.deliverer_price,express_count,take_orders.order_status,take_orders.order_cancel_status,take_orders.postscript,take_orders.payment,take_orders.created_at,users.nickname,users.avatar_url,users.phone,deliverers.nickname as deliverer_nickname,deliverers.avatar_url as deliverer_avatar_url,deliverers.phone as deliverer_phone'))
            ->where('take_orders.id',$id)
            ->first();
        $take_order->friendly_date = friendly_date($take_order->created_at);
        $take_order->expresses = app(TakeOrderExpressRepository::class)->where('take_order_id',$take_order->id)
            ->orderBy('id','asc')->get(['take_place','address']);
        return $take_order;
    }
    public function getOrderDetail($id)
    {
        $user = User::tokenAuth();
        $take_order = $this->find($id,['id','order_sn','user_id','deliverer_id','urgent','urgent_price','tip','coupon_id','coupon_name','coupon_price','original_price','total_price','order_status','order_cancel_status','express_count','express_price','deliverer_price','postscript','created_at']);
        $take_order->friendly_date = friendly_date($take_order->created_at);
        $take_order_data = $take_order->toArray();
        $take_order_expresses = app(TakeOrderExpressRepository::class)->getExpresses($take_order->id);
        $take_order_data['expresses'] = $take_order_expresses->toArray();

        if(in_array($take_order->order_status,['unpaid']))
        {
            throw new OutputServerMessageException("该订单无效");
        }

        $take_order_user = app(UserRepository::class)->find($take_order->user_id);
        $take_order_deliverer = app(UserRepository::class)->where('id',$take_order->deliverer_id)->first();

        $user_field = ['id','avatar_url','nickname'];

        if($take_order->deliverer_id == $user->id || $take_order->user_id == $user->id)
        {
            $user_field = array_merge($user_field,['phone']);

        }


        $take_order_user_data = visible_data($take_order_user->toArray(),$user_field);
        $take_order_deliverer_data = $take_order_deliverer ? visible_data($take_order_deliverer->toArray(),$user_field) : [];
        $data = [
            'take_order' => $take_order_data,
            'user' => $take_order_user_data,
            'deliverer' => $take_order_deliverer_data
        ];
        return $data;
    }
    public function acceptOrder($take_order)
    {
        $deliverer = User::tokenAuthCache();
        //检验接单人跟发单人是否为同一人
        if($take_order->user_id == $deliverer->id)
        {
            throw new OutputServerMessageException("不能接自己发的任务");
        }
        if ($take_order->order_status != 'new')
        {
            throw new \App\Exceptions\OutputServerMessageException('任务已被接');
        }
        if($take_order->created_at < date('Y-m-d 00:00:00'))
        {
            throw new \App\Exceptions\OutputServerMessageException('任务已过有效期');
        }
        try {
            $this->updateOrderStatus([
                'order_status' => 'accepted',
                'deliverer_id' => $deliverer->id,
            ],$take_order->id);
            app(TaskOrderRepository::class)->where('type','take_order')->where('objective_id',$take_order->id)->updateData([
                'deliverer_id' => $deliverer->id
            ]);
            //消息推送 发单人
            $message_data = [
                'task_type'=> 'take_order',
                'order_sn' => $take_order->order_sn,
                'user_id' => $take_order->user_id,
                'nickname' => $deliverer->nickname,
                'type' => 'accept_order',
            ];
            app(MessageService::class)->sendMessage($message_data);
        } catch (Exception $e) {
            throw new \App\Exceptions\RequestFailedException('无法接受任务');
        }
        return $take_order;

    }
    public function updateOrderStatus($data,$id)
    {
        $this->update($data,$id);
        app(TaskOrderRepository::class)->where('type','take_order')->where('objective_id',$id)->updateData([
            'order_status' => $data['order_status']
        ]);
        TaskOrderStatusChange::create([
            'type' => 'take_order',
            'objective_model' => 'TakeOrder',
            'objective_id' => $id,
            'order_status' => $data['order_status'],
            'order_cancel_status' => isset($data['order_cancel_status']) ? $data['order_cancel_status'] : NULL,
        ]);
    }
    public function finishOrder($take_order)
    {
        $deliverer = User::tokenAuthCache();
        if ($take_order->order_status != 'accepted') {
            throw new \App\Exceptions\OutputServerMessageException('当前任务状态不允许完成任务');
        }
        $this->updateOrderStatus(['order_status' => 'finish'],$take_order->id);

        //通知 发单人
        $message_data = [
            'task_type'=> 'take_order',
            'order_sn' => $take_order->order_sn,
            'user_id' => $take_order->user_id,
            'type' => 'finish_order',
        ];
        app(MessageService::class)->sendMessage($message_data);
        return "success";
    }
    public function completeOrder($take_order)
    {
        if ($take_order->order_status != 'finish') {
            throw new \App\Exceptions\OutputServerMessageException('当前任务状态不允许结算任务');
        }

        $deliverer = app(UserRepository::class)->where('id',$take_order->deliverer_id)->first();
        $fee = get_fee($take_order->deliverer_price);
        $income = $take_order->deliverer_price - $fee;
        $new_balance = $deliverer->balance + $income;
        $balanceData = array(
            'user_id' => $deliverer->id,
            'balance' => $new_balance,
            'price'	=> $income,
            'out_trade_no' => $take_order->order_sn,
            'fee' => $fee,
            'type' => 1,
            'trade_type' => 'ACCEPT_TAKE_ORDER',
            'description' => '接代拿任务',
        );

        $trade_no = 'BALANCE-'.generate_order_sn();
        $trade = array(
            'user_id' => $deliverer->id,
            'out_trade_no' => $take_order->order_sn,
            'trade_no' => $trade_no,
            'trade_status' => 'income',
            'type' => 1,
            'pay_from' => 'TakeOrder',
            'trade_type' => 'ACCEPT_TAKE_ORDER',
            'price' => $income,
            'fee' => $fee,
            'payment' => $take_order->payment,
            'description' => '接代拿任务',
        );

        $this->updateOrderStatus(['order_status' => 'completed','fee' => $fee],$take_order->id);

        app(UserRepository::class)->update(['balance' => $new_balance],$deliverer->id);
        app(BalanceRecordRepository::class)->create($balanceData);

        app(TradeRecordRepository::class)->create($trade);

        //通知 接单人
        $message_data = [
            'task_type'=> 'take_order',
            'order_sn' => $take_order->order_sn,
            'user_id' => $take_order->deliverer_id,
            'type' => 'complete_order',
        ];
        app(MessageService::class)->sendMessage($message_data);

        return "success";
    }
    public function userCancelOrder($take_order)
    {
        if ($take_order->order_status == 'accepted') {
            throw new \App\Exceptions\OutputServerMessageException('已被接单，请联系骑手取消任务');
        }
        if ($take_order->order_status != 'new') {
            throw new \App\Exceptions\OutputServerMessageException('当前任务状态不允许取消');
        }
        $data = [
            'id' => $take_order->id,
            'total_price' => $take_order->total_price,
            'order_sn' => $take_order->order_sn,
            'payment' => $take_order->payment,
            'coupon_id' => $take_order->coupon_id,
            'coupon_price' => $take_order->coupon_price,
            'trade_type' => 'CANCEL_TAKE_ORDER',
            'description' => '取消代拿任务',
        ];
        app(RefundService::class)->refundHandle($data,'TakeOrder',User::tokenAuth());
    }
    public function delivererCancelOrder($take_order)
    {
        if ($take_order->order_status != 'accepted') {
            throw new \App\Exceptions\OutputServerMessageException('当前任务状态不允许取消');
        }
        $this->updateOrderStatus(['order_status' => 'cancel','order_cancel_status' => 'deliverer_apply_cancel'],$take_order->id);

        //通知 发单人
        $message_data = [
            'task_type'=> 'take_order',
            'order_sn' => $take_order->order_sn,
            'user_id' => $take_order->user_id,
            'type' => 'deliverer_cancel_order',
        ];
        app(MessageService::class)->sendMessage($message_data);

        throw new \App\Exceptions\RequestSuccessException("操作成功，请等待或联系用户确认！");
    }
    public function agreeCancelOrder($take_order)
    {
        $user = User::tokenAuth();
        if ($take_order->order_cancel_status != 'deliverer_apply_cancel') {
            throw new \App\Exceptions\OutputServerMessageException('当前任务状态不允许同意取消');
        }
        $this->updateOrderStatus(['order_status' => 'cancel','order_cancel_status' => 'user_agree_cancel'],$take_order->id);
        $data = [
            'id' => $take_order->id,
            'total_price' => $take_order->total_price,
            'order_sn' =>  $take_order->order_sn,
            'payment' => $take_order->payment,
            'coupon_id' => $take_order->coupon_id,
            'coupon_price' => $take_order->coupon_price,
            'trade_type' => 'CANCEL_TAKE_ORDER',
            'description' => '取消代拿任务',
        ];
        app(RefundService::class)->refundHandle($data,'TakeOrder',User::tokenAuth());
        //通知 接单人
        $message_data = [
            'task_type'=> 'take_order',
            'order_sn' => $take_order->order_sn,
            'user_id' => $take_order->deliverer_id,
            'type' => 'user_agree_cancel_order',
        ];
        app(MessageService::class)->sendMessage($message_data);
        throw new \App\Exceptions\RequestSuccessException(trans("task.refund_success"));
    }


}
