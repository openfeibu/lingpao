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
            ->select(DB::raw('custom_orders.id,custom_orders.order_sn,custom_orders.user_id,custom_orders.deliverer_id,custom_orders.total_price,custom_orders.deliverer_price,custom_orders.order_status,custom_orders.order_cancel_status,custom_orders.postscript,custom_orders.created_at,users.nickname,users.avatar_url'))
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

}
