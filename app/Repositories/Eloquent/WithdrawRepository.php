<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\WithdrawRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Models\User;

class WithdrawRepository extends BaseRepository implements WithdrawRepositoryInterface
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
        return config('model.user.withdraw.model');
    }

}
