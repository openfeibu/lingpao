<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ResourceController as BaseController;
use App\Http\Requests\PageCategoryRequest;
use App\Repositories\Eloquent\PageCategoryRepositoryInterface;
use App\Models\PageCategory;
use Tree;
/**
 * Resource controller class for page.
 */
class PageCategoryResourceController extends BaseController
{
    /**
     * Initialize category resource controller.
     *
     * @param type PageCategoryRepositoryInterface $category
     *
     * @return null
     */
    public function __construct(PageCategoryRepositoryInterface $category)
    {
        parent::__construct();
        $this->repository = $category;
        $this->repository
            ->pushCriteria(\App\Repositories\Criteria\RequestCriteria::class)
            ->pushCriteria(\App\Repositories\Criteria\PageCategoryResourceCriteria::class);
    }

    public function index(PageCategoryRequest $request)
    {
        $categories = $this->repository
            ->orderBy('parent_id','asc')
            ->orderBy('order','asc')
            ->all()
            ->toArray();
        $categories = Tree::getTree($categories);
        $data['data'] = $categories;
        if ($this->response->typeIs('json'))
        {
            return $this->response
                ->data($data)
                ->output();
        }
        return $this->response->title(trans('page::category.names'))
            ->view('page.category.index', true)
            ->data($data)
            ->output();
    }

    public function show(PageCategoryRequest $request, Category $category)
    {
        if ($category->exists) {
            $view = 'page::admin.category.show';
        } else {
            $view = 'page::admin.category.new';
        }
        return $this->response->title(trans('app.view') . ' ' . trans('page::category.name'))
            ->data(compact('page'))
            ->view($view)
            ->output();
    }
    public function create()
    {

    }
}