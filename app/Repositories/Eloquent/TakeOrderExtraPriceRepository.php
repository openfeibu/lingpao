<?php

namespace App\Repositories\Eloquent;

use App\Exceptions\OutputServerMessageException;
use App\Repositories\Eloquent\TakeOrderExtraPriceRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Models\TakeOrderExtraPrice;
use Request,DB;

class TakeOrderExtraPriceRepository extends BaseRepository implements TakeOrderExtraPriceRepositoryInterface
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
        return config('model.take_order.take_order_extra_price.model');
    }


}
