<?php

namespace App\Http\Controllers\Api;

use App\Repositories\Eloquent\NavRepositoryInterface;
use App\Repositories\Eloquent\NavCategoryRepositoryInterface;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use App\Models\Nav;

class NavController extends BaseController
{
    public function __construct(NavRepositoryInterface $nav,
                                NavCategoryRepositoryInterface $category_repository)
    {
        parent::__construct();
        $this->repository = $nav;
        $this->category_repository = $category_repository;
    }
    public function getNavs(Request $request)
    {
        $navs = $this->repository;
        if(isset($request->category_slug) && !empty($request->category_slug))
        {
            $category = $this->category_repository->where(['slug' => $request->category_slug])->first(['id']);
            if($category)
            {
                $navs = $navs->where(['category_id' => $category->id]);
            }
        }
        $navs = $navs
            ->orderBy('order','asc')
            ->orderBy('id','asc')
            ->setPresenter(\App\Repositories\Presenter\Api\NavListPresenter::class)
            ->all();
        return [
            'code' => '200',
            'meta_title' => '',
            'meta_keyword' => '',
            'meta_description' => '',
            'data' => $navs['data'],
        ];
    }
}