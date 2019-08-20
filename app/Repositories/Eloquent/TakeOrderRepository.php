<?php

namespace App\Repositories\Eloquent;

use App\Exceptions\OutputServerMessageException;
use App\Repositories\Eloquent\TakeOrderRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Models\User;
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
    public function getOrders()
    {
        $limit = Request::get('limit',config('app.limit'));
        $take_orders = $this->model->join('users','users.id','=','take_orders.user_id')
            ->select(DB::raw('take_orders.id,take_orders.order_sn,take_orders.user_id,take_orders.deliverer_id,take_orders.urgent,take_orders.total_price,take_orders.deliverer_price,take_orders.express_count,take_orders.order_status,take_orders.postscript,take_orders.created_at,CASE take_orders.order_status WHEN "new" THEN 1 ELSE 2 END as status_num,users.nickname,users.avatar_url'))
            ->whereIn('take_orders.order_status', ['new','accepted','finish','completed'])
            //->where('take_orders.created_at','>',date("Y-m-d 00:00:00"))
            ->orderBy('status_num','asc')
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
            ->select(DB::raw('take_orders.id,take_orders.order_sn,take_orders.user_id,take_orders.deliverer_id,take_orders.urgent,take_orders.total_price,take_orders.deliverer_price,express_count,take_orders.order_status,take_orders.postscript,take_orders.created_at,users.nickname,users.avatar_url'))
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
        $take_order = $this->find($id,['id','order_sn','user_id','deliverer_id','urgent','urgent_price','tip','coupon_id','coupon_name','coupon_price','original_price','total_price','order_status','express_count','express_price','deliverer_price','postscript','created_at']);
        $take_order->friendly_date = friendly_date($take_order->created_at);
        $take_order_data = $take_order->toArray();
        $take_order_expresses = app(TakeOrderExpressRepository::class)->where('take_order_id',$take_order->id)
            ->orderBy('id','asc')
            ->get();

        if(in_array($take_order->order_status,['unpaid']))
        {
            throw OutputServerMessageException("该订单无效");
        }

        $take_order_user = app(UserRepository::class)->find($take_order->user_id);
        $take_order_deliverer = app(UserRepository::class)->where('id',$take_order->deliverer_id)->first();

        $user_field = ['id','avatar_url','nickname'];
        $take_order_expresses_field = ['take_place','address'];
        if($take_order->deliverer_id == $user->id || $take_order->user_id == $user->id)
        {
            $user_field = array_merge($user_field,['phone']);
            $take_order_expresses_field = ['take_place','consignee','mobile','address','description','take_code','express_company','express_arrive_date'];
        }
        if($take_order->order_status == 'new')
        {
            foreach ($take_order_expresses as $key => $take_order_express)
            {
                $take_order_expresses_data[] = visible_data($take_order_express->toArray(),$take_order_expresses_field);
            }
            $take_order_data['expresses'] = $take_order_expresses_data;
        }
        else{
            $take_order_data['expresses'] = $take_order_expresses->toArray();
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
        //检验接单人跟发单人是否为同一人
        if($take_order->user_id == User::tokenAuthCache()->id)
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
            $this->update([
                'order_status' => 'accepted',
                'deliverer_id' => User::tokenAuthCache()->id,
            ],$take_order->id);

            //TODO:消息推送

        } catch (Exception $e) {
            throw new \App\Exceptions\RequestFailedException('无法接受任务');
        }
        return $take_order;

    }

}
