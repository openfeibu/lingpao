<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\RemarkRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Models\User;

class RemarkRepository extends BaseRepository implements RemarkRepositoryInterface
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
        return config('model.remark.remark.model');
    }

}
