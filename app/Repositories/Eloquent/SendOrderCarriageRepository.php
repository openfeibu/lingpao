<?php

namespace App\Repositories\Eloquent;

use App\Exceptions\OutputServerMessageException;
use App\Repositories\Eloquent\SendOrderCarriageRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Models\TakeOrderExtraPrice;
use Request,DB;

class SendOrderCarriageRepository extends BaseRepository implements SendOrderCarriageRepositoryInterface
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
        return config('model.send_order.send_order_carriage.model');
    }

}
