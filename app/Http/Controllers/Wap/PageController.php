<?php

namespace App\Http\Controllers\Wap;

use App\Http\Controllers\Wap\Controller as BaseController;
use App\Repositories\Eloquent\PageCategoryRepositoryInterface;
use App\Repositories\Eloquent\PageRepositoryInterface;
use App\Repositories\Eloquent\SettingRepositoryInterface;
use Illuminate\Http\Request;
use App\Models\Page;

class PageController extends BaseController
{
    public function __construct(PageRepositoryInterface $page,
                                PageCategoryRepositoryInterface $category_repository,
                                SettingRepositoryInterface $setting_repository)
    {
        parent::__construct();
        $this->repository = $page;
        $this->category_repository = $category_repository;
        $this->setting_repository = $setting_repository;
        $this->repository
            ->pushCriteria(\App\Repositories\Criteria\PageResourceCriteria::class);
    }

    public function getPageSlug(Request $request,$slug)
    {
        $data = $this->repository
            ->where(['status' => 'show','slug' => $slug])
            ->first(['title','content']);
        if(!$data)
        {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('数据不存在');
        }
        $page = $data->toArray();

        return $this->response->title($page['title'])
            ->view('page')
            ->data(compact('page'))
            ->output();

    }

}
