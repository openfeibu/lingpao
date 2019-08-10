<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\PageCategoryRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class PageCategoryRepository extends BaseRepository implements PageCategoryRepositoryInterface
{

    /**
     * Booting the repository.
     *
     * @return null
     */
    public function boot()
    {
        $this->fieldSearchable = config('model.page.page.search');
    }

    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return config('model.page.category.model');
    }

    /**
     * Get page by id or slug.
     *
     * @return void
     */
    public function getPage($var)
    {
        return $this->findBySlug($var);
    }
}
