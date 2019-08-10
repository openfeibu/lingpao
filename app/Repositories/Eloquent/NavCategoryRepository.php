<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\NavCategoryRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class NavCategoryRepository extends BaseRepository implements NavCategoryRepositoryInterface
{

    /**
     * Booting the repository.
     *
     * @return null
     */
    public function boot()
    {
        $this->fieldSearchable = config('model.nav.nav.search');
    }

    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return config('model.nav.category.model');
    }

}
