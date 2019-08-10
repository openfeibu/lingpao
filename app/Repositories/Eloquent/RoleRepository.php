<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\RoleRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{


    public function boot()
    {
        $this->fieldSearchable = config('model.roles.role.model.search');

    }

    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return config('model.roles.role.model.model');
    }

    /**
     * Find a user by its key.
     *
     * @param type $key
     *
     * @return type
     */
    public function findRoleBySlug($key)
    {
        return $this->model->whereSlug($key)->first();
    }
}
