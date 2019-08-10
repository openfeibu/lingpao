<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ResourceController as BaseController;
use App\Models\Banner;
use App\Repositories\Eloquent\BannerRepositoryInterface;
use Illuminate\Http\Request;
use Mockery\CountValidator\Exception;

/**
 * Resource controller class for page.
 */
class BannerResourceController extends BaseController
{
    /**
     * Initialize page resource controller.
     *
     * @param type BannerRepositoryInterface $banner
     *
     */
    public function __construct(BannerRepositoryInterface $banner)
    {
        parent::__construct();
        $this->repository = $banner;
        $this->repository
            ->pushCriteria(\App\Repositories\Criteria\RequestCriteria::class);
    }
    public function index(Request $request){
        if ($this->response->typeIs('json')) {
            $data = $this->repository
                ->setPresenter(\App\Repositories\Presenter\BannerListPresenter::class)
                ->orderBy('order','asc')
                ->orderBy('id','asc')
                ->all();
            return $this->response
                ->success()
                ->data($data['data'])
                ->output();
        }
        return $this->response->title(trans('app.admin.panel'))
            ->view('banner.index')
            ->output();
    }
    public function create(Request $request)
    {
        $banner = $this->repository->newInstance([]);

        return $this->response->title(trans('app.admin.panel'))
            ->view('banner.create')
            ->data(compact('banner'))
            ->output();
    }
    public function store(Request $request)
    {
        try {
            $attributes = $request->all();

            $banner = $this->repository->create($attributes);

            return $this->response->message(trans('messages.success.created', ['Module' => trans('banner.name')]))
                ->code(0)
                ->status('success')
                ->url(guard_url('banner/' ))
                ->redirect();
        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('banner/'))
                ->redirect();
        }
    }
    public function show(Request $request,Banner $banner)
    {
        if ($banner->exists) {
            $view = 'banner.show';
        } else {
            $view = 'banner.new';
        }

        return $this->response->title(trans('app.view') . ' ' . trans('banner.name'))
            ->data(compact('banner'))
            ->view($view)
            ->output();
    }
    public function update(Request $request,Banner $banner)
    {
        try {
            $attributes = $request->all();

            $banner->update($attributes);

            return $this->response->message(trans('messages.success.created', ['Module' => trans('banner.name')]))
                ->code(0)
                ->status('success')
                ->url(guard_url('banner/'))
                ->redirect();
        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('banner/'))
                ->redirect();
        }
    }
    public function destroy(Request $request,Banner $banner)
    {
        try {
            $banner->forceDelete();

            return $this->response->message(trans('messages.success.deleted', ['Module' => trans('banner.name')]))
                ->status("success")
                ->http_code(202)
                ->url(guard_url('banner'))
                ->redirect();

        } catch (Exception $e) {

            return $this->response->message($e->getMessage())
                ->status("error")
                ->code(400)
                ->url(guard_url('banner'))
                ->redirect();
        }
    }
    public function destroyAll(Request $request)
    {
        try {
            $data = $request->all();
            $ids = $data['ids'];
            $this->repository->forceDelete($ids);

            return $this->response->message(trans('messages.success.deleted', ['Module' => trans('banner.name')]))
                ->status("success")
                ->http_code(202)
                ->url(guard_url('banner'))
                ->redirect();

        } catch (Exception $e) {

            return $this->response->message($e->getMessage())
                ->status("error")
                ->code(400)
                ->url(guard_url('banner'))
                ->redirect();
        }
    }

}