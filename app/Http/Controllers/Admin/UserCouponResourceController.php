<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ResourceController as BaseController;
use App\Models\UserCoupon;
use App\Repositories\Eloquent\UserCouponRepositoryInterface;
use Illuminate\Http\Request;
use Mockery\CountValidator\Exception;

/**
 * Resource controller class for page.
 */
class UserCouponResourceController extends BaseController
{
    /**
     * Initialize page resource controller.
     *
     * @param type UserCouponRepositoryInterface $user_coupon
     *
     */
    public function __construct(UserCouponRepositoryInterface $user_coupon)
    {
        parent::__construct();
        $this->repository = $user_coupon;
        $this->repository
            ->pushCriteria(\App\Repositories\Criteria\RequestCriteria::class);
    }
    public function index(Request $request){
        $limit = $request->input('limit',config('app.limit'));
        $search = $request->input('search',[]);
        $search_name = isset($search['search_name']) ? $search['search_name'] : '';
        $coupon_id = isset($request->coupon_id) ? $request->coupon_id : '';

        if ($this->response->typeIs('json')) {
            $user_coupons = $this->repository->join('users','users.id','user_coupons.user_id');
            if(!empty($search_name))
            {
                $user_coupons = $user_coupons->where(function ($query) use ($search_name){
                    return $query->where('users.phone','like','%'.$search_name.'%')->orWhere('users.nickname','like','%'.$search_name.'%')->orWhere('users.id',$search_name);
                });
            }

            if(!empty($coupon_id))
            {
                $user_coupons->where('coupon_id',$coupon_id);
            }
            $user_coupons = $user_coupons->orderBy('id','desc')
                ->paginate($limit,['user_coupons.*','users.nickname','users.avatar_url','users.phone']);

            return $this->response
                ->success()
                ->count($user_coupons->total())
                ->data($user_coupons->toArray()['data'])
                ->output();
        }
        return $this->response->title(trans('app.admin.panel'))
            ->view('user_coupon.index')
            ->data(compact('coupon_id'))
            ->output();
    }

}