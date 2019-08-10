<?php

namespace App\Http\Controllers\Api;

use App\Repositories\Eloquent\PageCategoryRepositoryInterface;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;

class PageCategoryController extends BaseController
{
    public function __construct(PageCategoryRepositoryInterface $category)
    {
        parent::__construct();
        $this->repository = $category;
        $this->repository
            ->pushCriteria(\App\Repositories\Criteria\RequestCriteria::class)
            ->pushCriteria(\App\Repositories\Criteria\PageCategoryResourceCriteria::class);
    }
    public function index(Request $request)
    {
        $pslug = $request->input('pslug');
        $parent_id = $request->input('parent_id',0);
        if($pslug)
        {
            $pcate = $this->repository
                ->setPresenter(\App\Repositories\Presenter\PageCategoryListPresenter::class)
                ->where(['slug' => $pslug])
                ->first();
            $parent_id = $pcate['data']['id'];
        }
        $data = $this->repository
            ->setPresenter(\App\Repositories\Presenter\PageCategoryListPresenter::class)
            ->orderBy('order')
            ->findWhere(['parent_id' => $parent_id]);

        return [
            'code' => '200',
            'data' => $data['data'],
        ];
    }

}
