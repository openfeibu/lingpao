<?php

namespace App\Http\Controllers\Admin;

use Tree,Auth;
use App\Http\Controllers\Admin\ResourceController as BaseController;
use App\Http\Requests\PermissionRequest;
use App\Repositories\Eloquent\PermissionRepositoryInterface;
use App\Models\Permission;
/**
 * Resource controller class for permission.
 */
class PermissionResourceController extends BaseController
{

    /**
     * Initialize permission resource controller.
     *
     * @param type PermissionRepositoryInterface $permission
     *
     * @return null
     */
    public function __construct(PermissionRepositoryInterface $permission)
    {
        parent::__construct();
        $this->repository = $permission;
        $this->repository
            ->pushCriteria(\App\Repositories\Criteria\RequestCriteria::class)
            ->pushCriteria(\App\Repositories\Criteria\PermissionResourceCriteria::class);
    }

    /**
     * @param PermissionRequest $request
     * @return mixed
     */
    public function index(PermissionRequest $request)
    {
        if ($this->response->typeIs('json')) {
            $data = $this->repository->orderBy('order','asc')->orderBy('id','asc')->all()->toArray();
            $data = Tree::getTree($data);
            return $this->response
                ->success()
                ->data($data)
                ->output();
        }

        return $this->response->title(trans('permission.names'))
            ->view('permission.index', true)
            ->output();
    }

    /**
     * @param PermissionRequest $request
     * @param Permission $permission
     * @return mixed
     */
    public function show(PermissionRequest $request, Permission $permission)
    {

        if ($permission->exists) {
            $view = 'permission.show';
        } else {
            $view = 'permission.new';
        }
        $father = Auth::user()->menus();

        return $this->response->title(trans('app.view') . ' ' . trans('permission.name'))
            ->data(compact('permission','father'))
            ->view($view, true)
            ->output();
    }

    /**
     * Show the form for creating a new permission.
     *
     * @param PermissionRequest $request
     *
     * @return Response
     */
    public function create(PermissionRequest $request)
    {
        $permission = $this->repository->newInstance([]);
        $father = Auth::user()->menus();

        return $this->response->title(trans('app.new') . ' ' . trans('permission.name')) 
            ->view('permission.create', true) 
            ->data(compact('permission','father'))
            ->output();
    }

    /**
     * Create new permission.
     *
     * @param PermissionRequest $request
     *
     * @return Response
     */
    public function store(PermissionRequest $request)
    {
        try {
            $attributes              = $request->all();
            $attributes['user_id']   = user_id();
            $attributes['user_type'] = user_type();
            $permission                 = $this->repository->create($attributes);

            return $this->response->message(trans('messages.success.created', ['Module' => trans('permission.name')]))
                ->http_code(204)
                ->status('success')
                ->url(guard_url('permission/' . $permission->id))
                ->redirect();
        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('/permission'))
                ->redirect();
        }

    }

    /**
     * Show permission for editing.
     *
     * @param PermissionRequest $request
     * @param Permission   $permission
     *
     * @return Response
     */
    public function edit(PermissionRequest $request, Permission $permission)
    {
        return $this->response->title(trans('app.edit') . ' ' . trans('permission.name'))
            ->view('permission.edit', true)
            ->data(compact('permission'))
            ->output();
    }

    /**
     * Update the permission.
     *
     * @param PermissionRequest $request
     * @param Permission   $permission
     *
     * @return Response
     */
    public function update(PermissionRequest $request, Permission $permission)
    {
        try {
            $attributes = $request->all();
            isset($attributes['name']) ? $attributes['name'] = trim($attributes['name'], chr(0xc2) . chr(0xa0)) : '';
            $permission->update($attributes);
            return $this->response->message(trans('messages.success.updated', ['Module' => trans('permission.name')]))
                ->code(0)
                ->status('success')
                ->url(guard_url('permission/' . $permission->id))
                ->redirect();
        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('permission/' . $permission->id))
                ->redirect();
        }

    }

    /**
     * @param PermissionRequest $request
     * @param Permission $permission
     * @return mixed
     */
    public function destroy(PermissionRequest $request, Permission $permission)
    {
        try {

            $permission->forceDelete();
            return $this->response->message(trans('messages.success.deleted', ['Module' => trans('permission.name')]))
                ->code(0)
                ->status('success')
                ->url(guard_url('permission'))
                ->redirect();

        } catch (Exception $e) {

            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('permission/' . $permission->id))
                ->redirect();
        }

    }

    /**
     * @param PermissionRequest $request
     * @return mixed
     */
    public function destroyAll(PermissionRequest $request)
    {
        try {
            $data = $request->all();
            $ids = $data['ids'];
            $this->repository->forceDelete($ids);

            return $this->response->message(trans('messages.success.deleted', ['Module' => trans('permission.name')]))
                ->status("success")
                ->http_code(202)
                ->url(guard_url('permission'))
                ->redirect();

        } catch (Exception $e) {

            return $this->response->message($e->getMessage())
                ->status("error")
                ->code(400)
                ->url(guard_url('permission'))
                ->redirect();
        }
    }

}
