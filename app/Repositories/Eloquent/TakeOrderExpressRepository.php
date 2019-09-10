<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\TakeOrderExpressRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Models\User;

class TakeOrderExpressRepository extends BaseRepository implements TakeOrderExpressRepositoryInterface
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
        return config('model.take_order.take_order_express.model');
    }

    public function getExpresses($take_order_id)
    {
        $take_order = app(TakeOrderRepository::class)->find($take_order_id,['user_id','deliverer_id']);

        $user_id = User::isLogin();
        if(!$user_id || ($user_id != $take_order->user_id && $$user_id != $take_order->deliverer_id))
        {
            $expresses = $this->where('take_order_id',$take_order_id)
                ->orderBy('id','asc')
                ->get(['take_place','address']);
            foreach ($expresses as $key => $express)
            {
                $express->address = sensitive_address($express->address);
            }
            return $expresses;
        }else{
            return $this->where('take_order_id',$take_order_id)
                ->orderBy('id','asc')
                ->get(['take_place','consignee','mobile','address','description','take_code','express_company','express_arrive_date']);
        }
    }
}
