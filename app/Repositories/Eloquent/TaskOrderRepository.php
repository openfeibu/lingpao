<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\TaskOrderRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Models\User;

class TaskOrderRepository extends BaseRepository implements TaskOrderRepositoryInterface
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
        return config('model.task_order.task_order.model');
    }


}
