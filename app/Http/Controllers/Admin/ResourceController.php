<?php

namespace App\Http\Controllers\Admin;

use App\Models\CustomOrder;
use App\Models\TakeOrder;
use App\Models\User;
use Route;
use App\Http\Controllers\Admin\Controller as BaseController;
use App\Traits\AdminUser\AdminUserPages;
use App\Http\Response\ResourceResponse;
use App\Traits\Theme\ThemeAndViews;
use App\Traits\AdminUser\RoutesAndGuards;

class ResourceController extends BaseController
{
    use AdminUserPages,ThemeAndViews,RoutesAndGuards;

    public function __construct()
    {
        parent::__construct();
        if (!empty(app('auth')->getDefaultDriver())) {
            $this->middleware('auth:' . app('auth')->getDefaultDriver());
           // $this->middleware('role:' . $this->getGuardRoute());
            $this->middleware('permission:' .Route::currentRouteName());
            $this->middleware('active');
        }
        $this->response = app(ResourceResponse::class);
        $this->setTheme();
    }
    /**
     * Show dashboard for each user.
     *
     * @return \Illuminate\Http\Response
     */
    public function home()
    {
        $user_count = User::count();
        $take_order_count = TakeOrder::where('order_status','<>','unpaid')->count();
        $custom_order_count = CustomOrder::where('order_status','<>','unpaid')->count();
        $balance_sum = User::sum('balance');


        return $this->response->title(trans('app.name'))
            ->view('home')
            ->data(compact('user_count','take_order_count','custom_order_count','balance_sum'))
            ->output();
    }
    public function dashboard()
    {
        return $this->response->title('测试')
            ->view('dashboard')
            ->output();
    }
}
