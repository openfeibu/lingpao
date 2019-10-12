<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ResourceController as BaseController;
use App\Models\Coupon;
use App\Repositories\Eloquent\CouponRepositoryInterface;
use Illuminate\Http\Request;
use Mockery\CountValidator\Exception;

/**
 * Resource controller class for page.
 */
class CouponResourceController extends BaseController
{
    /**
     * Initialize page resource controller.
     *
     * @param type CouponRepositoryInterface $coupon
     *
     */
    public function __construct(CouponRepositoryInterface $coupon)
    {
        parent::__construct();
        $this->repository = $coupon;
        $this->repository
            ->pushCriteria(\App\Repositories\Criteria\RequestCriteria::class);
    }
    public function index(Request $request){
        if ($this->response->typeIs('json')) {
            $coupons = $this->repository
                ->orderBy('id','desc')
                ->get();
            return $this->response
                ->success()
                ->data($coupons->toArray())
                ->output();
        }
        return $this->response->title(trans('app.admin.panel'))
            ->view('coupon.index')
            ->output();
    }
    public function create(Request $request)
    {
        $coupon = $this->repository->newInstance([]);

        return $this->response->title(trans('app.admin.panel'))
            ->view('coupon.create')
            ->data(compact('coupon'))
            ->output();
    }
    public function store(Request $request)
    {
        try {
            $attributes = $request->all();
            $attributes['stock'] = $attributes['num'];
            $attributes['is_open'] = $attributes['is_open'] == 'on' ? '1' : 0;
            $coupon = $this->repository->create($attributes);

            return $this->response->message(trans('messages.success.created', ['Module' => trans('coupon.name')]))
                ->code(0)
                ->status('success')
                ->url(guard_url('coupon/' ))
                ->redirect();
        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('coupon/'))
                ->redirect();
        }
    }
    public function show(Request $request,Coupon $coupon)
    {
        if ($coupon->exists) {
            $view = 'coupon.show';
        } else {
            $view = 'coupon.new';
        }

        return $this->response->title(trans('app.view') . ' ' . trans('coupon.name'))
            ->data(compact('coupon'))
            ->view($view)
            ->output();
    }
    public function update(Request $request,Coupon $coupon)
    {
        try {
            $attributes = $request->all();

            $coupon->update($attributes);

            return $this->response->message(trans('messages.success.updated', ['Module' => trans('coupon.name')]))
                ->code(0)
                ->status('success')
                ->url(guard_url('coupon/'))
                ->redirect();
        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('coupon/'))
                ->redirect();
        }
    }
    public function destroy(Request $request,Coupon $coupon)
    {
        try {
            $coupon->forceDelete();

            return $this->response->message(trans('messages.success.deleted', ['Module' => trans('coupon.name')]))
                ->status("success")
                ->http_code(202)
                ->url(guard_url('coupon'))
                ->redirect();

        } catch (Exception $e) {

            return $this->response->message($e->getMessage())
                ->status("error")
                ->code(400)
                ->url(guard_url('coupon'))
                ->redirect();
        }
    }
    public function destroyAll(Request $request)
    {
        try {
            $data = $request->all();
            $ids = $data['ids'];
            $this->repository->forceDelete($ids);

            return $this->response->message(trans('messages.success.deleted', ['Module' => trans('coupon.name')]))
                ->status("success")
                ->http_code(202)
                ->url(guard_url('coupon'))
                ->redirect();

        } catch (Exception $e) {

            return $this->response->message($e->getMessage())
                ->status("error")
                ->code(400)
                ->url(guard_url('coupon'))
                ->redirect();
        }
    }

}