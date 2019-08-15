<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\TakeOrderRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Models\User;

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


}
