<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\PageRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class PageRepository extends BaseRepository implements PageRepositoryInterface
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
        return config('model.page.page.model');
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
    public function getPages($slug,$count=10)
    {
        $category = PageCategory::where('slug',$slug)->first();
        $pages = app('page_repository')->where(['category_id' => $category->id])->orderBy('id','desc')->limit($count)->all();
        return $pages;
    }
    public function getHomeRecommendPages($slug,$count=10)
    {
        $category = PageCategory::where('slug',$slug)->first();
        $pages = app('page_repository')->where(['category_id' => $category->id,'recommend_type'=>'home'])->orderBy('id','desc')->limit($count)->all();
        return $pages;
    }
}
