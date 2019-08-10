<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ResourceController as BaseController;
use App\Http\Requests\RoleRequest;
use Illuminate\Http\Request;
use App\Repositories\Eloquent\PermissionRepositoryInterface;
use App\Repositories\Eloquent\RoleRepositoryInterface;
use App\Models\Role;

/**
 * Resource controller class for role.
 */
class RoleResourceController extends BaseController
{

    /**
     * Initialize role resource controller.
     *
     * @param type RoleRepositoryInterface $role
     * @param type PermissionRepositoryInterface $permission
     *
     */
    public function __construct(
        RoleRepositoryInterface       $role,
        PermissionRepositoryInterface $permission
    ) {
        parent::__construct();
        $this->repository = $role;
        $this->permission = $permission;
        $this->repository
            ->pushCriteria(\App\Repositories\Criteria\RequestCriteria::class)
            ->pushCriteria(\App\Repositories\Criteria\RoleResourceCriteria::class);
    }

    /**
     * Display a list of role.
     * @param RoleRequest $request
     * @return Response
     */
    public function index(RoleRequest $request)
    {
        $limit = $request->input('limit',config('app.limit'));

        if ($this->response->typeIs('json')) {

            $data = $this->repository
                ->orderBy('id','asc')
                ->setPresenter(\App\Repositories\Presenter\RoleListPresenter::class)
                ->getDataTable($limit);
            return $this->response
                ->success()
                ->count($data['recordsTotal'])
                ->data($data['data'])
                ->output();
        }

        return $this->response->title(trans('role.names'))
            ->view('role.index', true)
            ->output();
    }

    /**
     * Display role.
     *
     * @param RoleRequest $request
     * @param Role   $role
     *
     * @return Response
     */
    public function show(RoleRequest $request, Role $role)
    {
        if ($role->exists) {
            $view = 'role.show';
        } else {
            $view = 'role.new';
        }
        $permissions = $this->permission->allPermissions();
        return $this->response->title(trans('app.view') . ' ' . trans('role.name'))
            ->data(compact('role', 'permissions'))
            ->view($view, true)
            ->output();
    }

    /**
     * Show the form for creating a new role.
     *
     * @param RoleRequest $request
     *
     * @return Response
     */
    public function create(RoleRequest $request)
    {
        $permissions = $this->permission->allPermissions();
        $role = $this->repository->newInstance([]);
        return $this->response->title(trans('app.new') . ' ' . trans('role.name'))
            ->view('role.create', true)
            ->data(compact('role', 'permissions'))
            ->output();
    }

    /**
     * Create new role.
     *
     * @param RoleRequest $request
     *
     * @return Response
     */
    public function store(RoleRequest $request)
    {
        try {
            $attributes              = $request->all();
            $attributes['user_id']   = user_id();
            $attributes['user_type'] = user_type();
            $role                    = $this->repository->create($attributes);
            $role->permissions()->sync($attributes['permissions']);
            return $this->response->message(trans('messages.success.created', ['Module' => trans('role.name')]))
                ->code(0)
                ->status('success')
                ->url(guard_url('role/' . $role->id))
                ->redirect();
        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('/role'))
                ->redirect();
        }

    }

    /**
     * Show role for editing.
     *
     * @param RoleRequest $request
     * @param Role   $role
     *
     * @return Response
     */
    public function edit(RoleRequest $request, Role $role)
    {
        $permissions     = $this->permission->groupedPermissions(true);
        return $this->response->title(trans('app.edit') . ' ' . trans('roles::role.name'))
            ->view('roles::role.edit', true)
            ->data(compact('role', 'permissions'))
            ->output();
    }

    /**
     * Update the role.
     *
     * @param RoleRequest $request
     * @param Role   $role
     *
     * @return Response
     */
    public function update(RoleRequest $request, Role $role)
    {
        try {
            $attributes = $request->all();
            $permissions = $request->input('permissions');
            $role->update($attributes);
            $role->permissions()->sync($permissions);

            return $this->response->message(trans('messages.success.updated', ['Module' => trans('role.name')]))
                ->code(0)
                ->status('success')
                ->url(guard_url('role/' . $role->id))
                ->redirect();
        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('role/' . $role->id))
                ->redirect();
        }

    }

    /**
     * Remove the role.
     *
     * @param RoleRequest $request
     * @param Role   $role
     *
     * @return Response
     */
    public function destroy(RoleRequest $request, Role $role)
    {
        try {
            $role->forceDelete();
            return $this->response->message(trans('messages.success.deleted', ['Module' => trans('role.name')]))
                ->http_code(202)
                ->status('success')
                ->url(guard_url('role'))
                ->redirect();

        } catch (Exception $e) {

            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('role'))
                ->redirect();
        }

    }
    public function destroyAll(Request $request)
    {
        try {
            $data = $request->all();
            $ids = $data['ids'];
            $this->repository->forceDelete($ids);

            return $this->response->message(trans('messages.success.deleted', ['Module' => trans('role.name')]))
                ->status("success")
                ->http_code(202)
                ->url(guard_url('role'))
                ->redirect();

        } catch (Exception $e) {

            return $this->response->message($e->getMessage())
                ->status("error")
                ->code(400)
                ->url(guard_url('role'))
                ->redirect();
        }
    }

}
