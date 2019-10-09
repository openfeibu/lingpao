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
        $today_user_count = User::where('created_at','>=',date('Y-m-d 00:00:00'))->count();
        $deliverer_count = User::whereIn('role',['deliverer','expert_deliverer'])->count();


        $take_order_count = TakeOrder::where('order_status','<>','unpaid')->count();
        $today_take_order_count = TakeOrder::where('order_status','<>','unpaid')->where('created_at','>=',date('Y-m-d 00:00:00'))->count();
        $custom_order_count = CustomOrder::where('order_status','<>','unpaid')->count();
        $today_custom_order_count = CustomOrder::where('order_status','<>','unpaid')->where('created_at','>=',date('Y-m-d 00:00:00'))->count();
        $send_order_count = 0;
        $today_send_order_count = 0;

        $balance_sum = User::sum('balance');
        $take_order_total_price = TakeOrder::where('order_status','<>','unpaid')->sum('total_price');
        $custom_order_total_price = CustomOrder::where('order_status','<>','unpaid')->sum('total_price');
        $send_order_total_price = 0;
        $transaction_total_price = $take_order_total_price + $custom_order_total_price + $send_order_total_price;

        $today_take_order_total_price = TakeOrder::where('order_status','<>','unpaid')->where('created_at','>=',date('Y-m-d 00:00:00'))->sum('total_price');
        $today_custom_order_total_price = CustomOrder::where('order_status','<>','unpaid')->where('created_at','>=',date('Y-m-d 00:00:00'))->sum('total_price');
        $today_send_order_total_price = 0;
        $today_transaction_total_price = $today_take_order_total_price + $today_custom_order_total_price + $today_send_order_total_price;


        return $this->response->title(trans('app.name'))
            ->view('home')
            ->data(compact('user_count','take_order_count','custom_order_count','balance_sum','today_take_order_count','today_custom_order_count','send_order_count','today_send_order_count','today_user_count','deliverer_count','transaction_total_price','today_transaction_total_price'))
            ->output();
    }
    public function dashboard()
    {
        return $this->response->title('测试')
            ->view('dashboard')
            ->output();
    }
}
