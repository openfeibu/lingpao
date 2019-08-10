<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\PageBaseResourceController as BaseController;
use App\Http\Requests\PageRequest;
use Illuminate\Http\Request;
use App\Repositories\Eloquent\PageRepositoryInterface;
use App\Repositories\Eloquent\PageCategoryRepositoryInterface;
use App\Models\Page;
/**
 * Resource controller class for page.
 */
class CaseResourceController extends BaseController
{
    public function __construct(PageRepositoryInterface $page,
                                PageCategoryRepositoryInterface $category_repository,
                                Request $request)
    {
        parent::__construct($page,$category_repository);
        $this->repository = $page;
        $this->category_repository = $category_repository;
        $this->request = $request;
        $this->category_slug = 'case';
        $this->main_url = 'case/case';
        if($category_name = $this->request->input('category_name',''))
        {
            $this->category_slug = $category_name;
        }
        $category_data = $category_repository->where(['slug' => $this->category_slug])->first();
        $this->category_data = $category_data;
        $this->category_id = $category_data['id'];
        $this->repository = $this->repository
            ->pushCriteria(\App\Repositories\Criteria\RequestCriteria::class)
            ->pushCriteria(\App\Repositories\Criteria\PageResourceCriteria::class);
    }
    public function show(PageRequest $request,Page $case)
    {
        return parent::show($request,$case);
    }
    public function update(PageRequest $request,Page $case)
    {
        return parent::update($request,$case);
    }
    public function destroy(PageRequest $request,Page $case)
    {
        return parent::destroy($request,$case);
    }
}