<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ResourceController as BaseController;
use Auth;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Repositories\Eloquent\UserRepositoryInterface;

/**
 * Resource controller class for user.
 */
class UserResourceController extends BaseController
{

    /**
     * Initialize user resource controller.
     *
     * @param type UserRepositoryInterface $user
     */

    public function __construct(
        UserRepositoryInterface $user
    )
    {
        parent::__construct();
        $this->repository = $user;
        $this->repository
            ->pushCriteria(\App\Repositories\Criteria\RequestCriteria::class);
    }
    public function index(Request $request)
    {
        $limit = $request->input('limit',config('app.limit'));
        $search = $request->input('search',[]);
        $search_name = isset($search['search_name']) ? $search['search_name'] : '';
        $role = isset($search['role']) ? $search['role'] : '';
        if ($this->response->typeIs('json')) {
            $users = $this->repository;
            if(!empty($search_name))
            {
                $users = $users->where(function ($query) use ($search_name){
                    return $query->where('phone','like','%'.$search_name.'%')->orWhere('nickname','like','%'.$search_name.'%')->orWhere('id',$search_name);
                });
            }
            if(!empty($role))
            {
                $users = $users->where(function ($query) use ($role){
                    return $query->where('role',$role);
                });
            }
            $users = $users
                ->orderBy('id','desc')
                ->paginate($limit);

            return $this->response
                ->success()
                ->count($users->total())
                ->data($users->toArray()['data'])
                ->output();
        }
        return $this->response->title(trans('app.name'))
            ->view('user.index')
            ->output();
    }

    public function show(Request $request,User $user)
    {
        if ($user->exists) {
            $view = 'user.show';
        } else {
            $view = 'user.new';
        }
        return $this->response->title(trans('app.view'))
            ->data(compact('user'))
            ->view($view)
            ->output();
    }
    public function update(Request $request, User $user)
    {
        try {
            $attributes = $request->all();

            $user->update($attributes);

            return $this->response->message(trans('messages.success.updated', ['Module' => trans('user.name')]))
                ->code(0)
                ->status('success')
                ->url(guard_url('user/'))
                ->redirect();
        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('user/' . $user->id))
                ->redirect();
        }
    }

}