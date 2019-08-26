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

    public function updateOrderStatus($data,$id)
    {
        $this->update($data,$id);
        app(TaskOrderRepository::class)->where('type','custom_order')->where('objective_id',$id)->updateData([
            'order_status' => $data['order_status']
        ]);
    }

}
