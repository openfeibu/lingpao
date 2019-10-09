<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\CustomOrderCategoryRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use Request,DB;

class CustomOrderCategoryRepository extends BaseRepository implements CustomOrderCategoryRepositoryInterface
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
        return config('model.custom_order.custom_order_category.model');
    }

}
