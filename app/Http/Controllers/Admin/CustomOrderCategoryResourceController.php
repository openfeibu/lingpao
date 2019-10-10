<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\OutputServerMessageException;
use App\Http\Controllers\Admin\ResourceController as BaseController;
use App\Models\CustomOrderCategory;
use App\Repositories\Eloquent\CustomOrderCategoryRepositoryInterface;
use App\Repositories\Eloquent\CustomOrderRepositoryInterface;
use Illuminate\Http\Request;
use Mockery\CountValidator\Exception;

/**
 * Resource controller class for page.
 */
class CustomOrderCategoryResourceController extends BaseController
{
    /**
     * Initialize page resource controller.
     *
     * @param type CustomOrderCategoryRepositoryInterface $customOrderCategoryRepository
     *
     */
    public function __construct(CustomOrderCategoryRepositoryInterface $customOrderCategoryRepository,
                                CustomOrderRepositoryInterface $customOrderRepository)
    {
        parent::__construct();
        $this->repository = $customOrderCategoryRepository;
        $this->customOrderRepository = $customOrderRepository;
        $this->repository
            ->pushCriteria(\App\Repositories\Criteria\RequestCriteria::class);
    }
    public function index(Request $request){
        if ($this->response->typeIs('json')) {
            $categories = $this->repository
                ->orderBy('order','asc')
                ->orderBy('id','asc')
                ->get();
            //var_dump($categories->toArray());exit;
            return $this->response
                ->success()
                ->data($categories->toArray())
                ->output();
        }
        return $this->response->title(trans('app.admin.panel'))
            ->view('custom_order_category.index')
            ->output();
    }
    public function create(Request $request)
    {
        $custom_order_category = $this->repository->newInstance([]);

        return $this->response->title(trans('app.admin.panel'))
            ->view('custom_order_category.create')
            ->data(compact('custom_order_category'))
            ->output();
    }
    public function store(Request $request)
    {
        try {
            $attributes = $request->all();

            $custom_order_category = $this->repository->create($attributes);

            return $this->response->message(trans('messages.success.created', ['Module' => trans('custom_order_category.name')]))
                ->code(0)
                ->status('success')
                ->url(guard_url('custom_order_category/' ))
                ->redirect();
        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('custom_order_category/'))
                ->redirect();
        }
    }
    public function show(Request $request,CustomOrderCategory $custom_order_category)
    {
        if ($custom_order_category->exists) {
            $view = 'custom_order_category.show';
        } else {
            $view = 'custom_order_category.new';
        }

        return $this->response->title(trans('app.view') . ' ' . trans('custom_order_category.name'))
            ->data(compact('custom_order_category'))
            ->view($view)
            ->output();
    }
    public function update(Request $request,CustomOrderCategory $custom_order_category)
    {
        try {
            $attributes = $request->all();

            $custom_order_category->update($attributes);

            return $this->response->message(trans('messages.success.created', ['Module' => trans('custom_order_category.name')]))
                ->code(0)
                ->status('success')
                ->url(guard_url('custom_order_category/'))
                ->redirect();
        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('custom_order_category/'))
                ->redirect();
        }
    }
    public function destroy(Request $request,CustomOrderCategory $custom_order_category)
    {
        try {
            $exist = $this->customOrderRepository->where('custom_order_category_id',$custom_order_category->id)->first();
            if($exist)
            {
                throw new OutputServerMessageException("该分类下存在订单，请勿删除");
            }
            $custom_order_category->forceDelete();
            return $this->response->message(trans('messages.success.deleted', ['Module' => trans('custom_order_category.name')]))
                ->status("success")
                ->http_code(202)
                ->url(guard_url('custom_order_category'))
                ->redirect();

        } catch (Exception $e) {

            return $this->response->message($e->getMessage())
                ->status("error")
                ->code(400)
                ->url(guard_url('custom_order_category'))
                ->redirect();
        }
    }
    public function destroyAll(Request $request)
    {
        try {
            $data = $request->all();
            $ids = $data['ids'];
            $this->repository->forceDelete($ids);

            return $this->response->message(trans('messages.success.deleted', ['Module' => trans('custom_order_category.name')]))
                ->status("success")
                ->http_code(202)
                ->url(guard_url('custom_order_category'))
                ->redirect();

        } catch (Exception $e) {

            return $this->response->message($e->getMessage())
                ->status("error")
                ->code(400)
                ->url(guard_url('custom_order_category'))
                ->redirect();
        }
    }

}