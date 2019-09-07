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
        return $this->response->title(trans('app.view') . ' ' . trans('user.name'))
            ->data(compact('user'))
            ->view($view)
            ->output();
    }

    /**
     * Show the form for creating a new user.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function create(Request $request)
    {

        $user = $this->repository->newInstance([]);

        return $this->response->title(trans('app.new') . ' ' . trans('user.name'))
            ->view('user.create')
            ->data(compact('user'))
            ->output();
    }

    /**
     * Create new user.
     *
     * @param UserRequest $request
     *
     * @return Response
     */
    public function store(UserRequest $request)
    {
        try {
            $attributes              = $request->all();
            $attributes['payment_company_id'] = Auth::user()->payment_company_id;
            $attributes['provider_id'] = Auth::user()->provider_id;

            $user = $this->repository->create($attributes);

            return $this->response->message(trans('messages.success.created', ['Module' => trans('user.name')]))
                ->code(204)
                ->status('success')
                ->url(guard_url('user'))
                ->redirect();
        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('user'))
                ->redirect();
        }

    }

    /**
     * Update the user.
     *
     * @param Request $request
     * @param User   $user
     *
     * @return Response
     */
    public function update(Request $request, User $user)
    {
        try {
            $attributes = $request->all();
            $user->update($attributes);
            return $this->response->message(trans('messages.success.updated', ['Module' => trans('user.name')]))
                ->code(0)
                ->status('success')
                ->url(guard_url('user/' . $user->id))
                ->redirect();
        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('user/' . $user->id))
                ->redirect();
        }
    }

    /**
     * @param Request $request
     * @param User $user
     * @return mixed
     */
    public function destroy(Request $request, User $user)
    {
        try {
            $user->forceDelete();
            return $this->response->message(trans('messages.success.deleted', ['Module' => trans('user.name')]))
                ->code(202)
                ->status('success')
                ->url(guard_url('user'))
                ->redirect();

        } catch (Exception $e) {

            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('user'))
                ->redirect();
        }

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function destroyAll(Request $request)
    {
        try {
            $data = $request->all();
            $ids = $data['ids'];
            $this->repository->forceDelete($ids);

            return $this->response->message(trans('messages.success.deleted', ['Module' => trans('user.name')]))
                ->status("success")
                ->code(202)
                ->url(guard_url('user'))
                ->redirect();

        } catch (Exception $e) {

            return $this->response->message($e->getMessage())
                ->status("error")
                ->code(400)
                ->url(guard_url('user'))
                ->redirect();
        }
    }

    public function import(Request $request)
    {
        return $this->response->title(trans('user.name'))
            ->view('user.import')
            ->output();
    }

    public function submitImport(Request $request)
    {
        $res = app('excel_service')->uploadExcel();

        $excel_data = [];
        $payment_company_id = Auth::user()->payment_company_id;
        $provider_id = Auth::user()->provider_id;

        foreach ( $res as $k => $v ) {
            if($v['电话'])
            {
                $excel_data[$k] = [
                    'name' => isset($v['姓名']) ? $v['姓名'] : '',
                    'phone' => isset($v['电话']) ? trim($v['电话']) : '',
                    'wechat' => isset($v['微信']) ? $v['微信'] : '',
                    'payment_company_id' => $payment_company_id,
                    'provider_id' => $provider_id,
                    'password' => '123456'
                ];

                $this->repository->create($excel_data[$k]);
            }
        }

        return $this->response->message("上传数据成功")
            ->status("success")
            ->code(200)
            ->url(guard_url('user'))
            ->redirect();

    }
}