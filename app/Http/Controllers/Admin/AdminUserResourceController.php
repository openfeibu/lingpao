<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ResourceController as BaseController;
use App\Models\AdminUser;
use App\Repositories\Eloquent\PermissionRepositoryInterface;
use App\Repositories\Eloquent\RoleRepositoryInterface;
use App\Http\Requests\AdminUserRequest;
use App\Repositories\Eloquent\AdminUserRepositoryInterface;

/**
 * Resource controller class for user.
 */
class AdminUserResourceController extends BaseController
{

    /**
     * @var Permissions
     */
    protected $permission;

    /**
     * @var roles
     */
    protected $roles;

    /**
     * Initialize admin_user resource controller.
     *
     * @param type AdminUserRepositoryInterface $admin_user
     * @param type PermissionRepositoryInterface $permissions
     * @param type RoleRepositoryInterface $roles
     */

    public function __construct(
        AdminUserRepositoryInterface $admin_user,
        PermissionRepositoryInterface $permissions,
        RoleRepositoryInterface $roles
    )
    {
        parent::__construct();
        $this->permissions = $permissions;
        $this->roles = $roles;
        $this->repository = $admin_user;
        $this->repository
            ->pushCriteria(\App\Repositories\Criteria\RequestCriteria::class)
            ->pushCriteria(\App\Repositories\Criteria\AdminUserResourceCriteria::class);
    }
    public function index(AdminUserRequest $request)
    {
        $limit = $request->input('limit',config('app.limit'));
        $search = $request->input('search_name','');
        if ($this->response->typeIs('json')) {
            $data = $this->repository
                ->setPresenter(\App\Repositories\Presenter\AdminUserPresenter::class);
            if(!empty($search_name))
            {
                $data = $data->where(function ($query,$search_name){
                    $query->where('email','like','%'.$search_name.'%');
                });
            }
            $data = $data->orderBy('id','desc')
                ->getDataTable($limit);
            return $this->response
                ->success()
                ->count($data['recordsTotal'])
                ->data($data['data'])
                ->output();
        }
        return $this->response->title(trans('app.admin.panel'))
            ->view('admin_user.index')
            ->output();
    }

    public function show(AdminUserRequest $request,AdminUser $admin_user)
    {
        if ($admin_user->exists) {
            $view = 'admin_user.show';
        } else {
            $view = 'admin_user.new';
        }
        $roles = $this->roles->all();
        return $this->response->title(trans('app.view') . ' ' . trans('admin_user.name'))
            ->data(compact('admin_user','roles'))
            ->view($view)
            ->output();
    }

    /**
     * Show the form for creating a new user.
     *
     * @param AdminUserRequest $request
     *
     * @return Response
     */
    public function create(AdminUserRequest $request)
    {

        $admin_user = $this->repository->newInstance([]);
        $roles       = $this->roles->all();
        return $this->response->title(trans('app.new') . ' ' . trans('admin_user.name'))
            ->view('admin_user.create')
            ->data(compact('admin_user', 'roles'))
            ->output();
    }

    /**
     * Create new user.
     *
     * @param AdminUserRequest $request
     *
     * @return Response
     */
    public function store(AdminUserRequest $request)
    {
        try {
            $attributes              = $request->all();
            $roles          = $request->get('roles');
            $attributes['user_id']   = user_id();
            $attributes['user_type'] = user_type();
            $attributes['api_token'] = str_random(60);
            $admin_user = $this->repository->create($attributes);
            $admin_user->roles()->sync($roles);
            return $this->response->message(trans('messages.success.created', ['Module' => trans('admin_user.name')]))
                ->http_code(204)
                ->status('success')
                ->url(guard_url('admin_user'))
                ->redirect();
        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('admin_user/' . $admin_user->id))
                ->redirect();
        }

    }

    /**
     * Update the user.
     *
     * @param AdminUserRequest $request
     * @param AdminUser   $admin_user
     *
     * @return Response
     */
    public function update(AdminUserRequest $request, AdminUser $admin_user)
    {
        try {
            $attributes = $request->all();
            $roles          = $request->get('roles');
            $admin_user->update($attributes);
            $admin_user->roles()->sync($roles);
            return $this->response->message(trans('messages.success.updated', ['Module' => trans('admin_user.name')]))
                ->code(0)
                ->status('success')
                ->url(guard_url('admin_user/' . $admin_user->id))
                ->redirect();
        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('admin_user/' . $admin_user->id))
                ->redirect();
        }
    }

    /**
     * @param AdminUserRequest $request
     * @param AdminUser $admin_user
     * @return mixed
     */
    public function destroy(AdminUserRequest $request, AdminUser $admin_user)
    {
        try {

            $admin_user->forceDelete();
            return $this->response->message(trans('messages.success.deleted', ['Module' => trans('admin_user.name')]))
                ->http_code(202)
                ->status('success')
                ->url(guard_url('admin_user'))
                ->redirect();

        } catch (Exception $e) {

            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('admin_user/' . $admin_user->id))
                ->redirect();
        }

    }

    /**
     * @param AdminUserRequest $request
     * @return mixed
     */
    public function destroyAll(AdminUserRequest $request)
    {
        try {
            $data = $request->all();
            $ids = $data['ids'];
            $this->repository->forceDelete($ids);

            return $this->response->message(trans('messages.success.deleted', ['Module' => trans('admin_user.name')]))
                ->status("success")
                ->http_code(202)
                ->url(guard_url('admin_user'))
                ->redirect();

        } catch (Exception $e) {

            return $this->response->message($e->getMessage())
                ->status("error")
                ->code(400)
                ->url(guard_url('admin_user'))
                ->redirect();
        }
    }
}