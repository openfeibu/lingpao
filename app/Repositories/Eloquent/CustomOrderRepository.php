<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\CustomOrderRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
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
}
