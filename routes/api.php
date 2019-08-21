<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {
    $api->get('/','App\Http\Controllers\Api\HomeController@index');
    $api->post('login', 'App\Http\Controllers\Api\Auth\LoginController@login');
    $api->post('register', 'App\Http\Controllers\Api\Auth\RegisterController@register');
    $api->get('/page','App\Http\Controllers\Api\PageController@getPages');
    $api->get('/page/{id}','App\Http\Controllers\Api\PageController@getPage');
    $api->get('/page/slug/{slug}','App\Http\Controllers\Api\PageController@getPageSlug');
    $api->get('/page-category','App\Http\Controllers\Api\PageCategoryController@index');
    $api->get('/page-recruit','App\Http\Controllers\Api\PageController@getRecruits');
    $api->get('/page-contact','App\Http\Controllers\Api\PageController@getContacts');
    $api->get('/banners','App\Http\Controllers\Api\HomeController@getBanners');
    $api->get('/link','App\Http\Controllers\Api\LinkController@getLinks');
    $api->get('/nav','App\Http\Controllers\Api\NavController@getNavs');
    $api->post('test','App\Http\Controllers\Api\HomeController@test');
    $api->get('/link','App\Http\Controllers\Api\LinkController@getLinks');
    $api->get('/videoVid','App\Http\Controllers\Api\HomeController@getVideoVid');

    $api->get('/user_info','App\Http\Controllers\Api\UserController@getUser');

    $api->post('/weapp/code','App\Http\Controllers\Api\Auth\WeAppUserLoginController@code');
    $api->post('/weapp/login','App\Http\Controllers\Api\Auth\WeAppUserLoginController@login');
    $api->post('/user/submit_phone','App\Http\Controllers\Api\UserController@submitPhone');
    $api->post('/user/submit_location','App\Http\Controllers\Api\UserController@submitLocation');
    $api->post('/user/set_pay_password', 'App\Http\Controllers\Api\UserController@setPayPassword');
    $api->post('/user/change_pay_password', 'App\Http\Controllers\Api\UserController@changePayPassword');
    $api->get('/user_coupon', 'App\Http\Controllers\Api\UserCouponController@getUserCoupons');
    $api->get('/user/balance', 'App\Http\Controllers\Api\UserController@getBalance');
    //$api->post('/user/reset_password', 'App\Http\Controllers\Api\UserController@resetPassword');

    $api->get('/user_address','App\Http\Controllers\Api\UserAddressController@getUserAddresses');
    $api->get('/user_address/{id}','App\Http\Controllers\Api\UserAddressController@getUserAddress');
    $api->get('/user_default_address','App\Http\Controllers\Api\UserAddressController@getDefault');
    $api->post('/user_address/store','App\Http\Controllers\Api\UserAddressController@store');
    $api->post('/user_address/update','App\Http\Controllers\Api\UserAddressController@update');
    $api->post('/user_address/destroy','App\Http\Controllers\Api\UserAddressController@destroy');


    $api->post('/extract_express_info','App\Http\Controllers\Api\User\TakeOrderController@extractExpressInfo');

    $api->post('payment/wechat-notify','App\Http\Controllers\Api\PaymentController@wechatNotify');

    $api->get('/setting','App\Http\Controllers\Api\HomeController@setting');

    $api->get('/task_order','App\Http\Controllers\Api\TaskOrderController@getOrders');
    $api->get('/task_order/{id}','App\Http\Controllers\Api\TaskOrderController@getOrder');
    $api->get('/task_order/user/order','App\Http\Controllers\Api\TaskOrderController@getUserOrders');
    $api->get('/task_order/deliverer/order','App\Http\Controllers\Api\TaskOrderController@getDelivererOrders');

    $api->post('/take_order/create_order','App\Http\Controllers\Api\User\TakeOrderController@createOrder');
    $api->get('/take_order/order','App\Http\Controllers\Api\User\TakeOrderController@getOrders');
    $api->get('/take_order/order/{id}','App\Http\Controllers\Api\User\TakeOrderController@getOrder');
    $api->post('/take_order/accept_order','App\Http\Controllers\Api\Deliverer\TakeOrderController@acceptOrder');
    $api->post('/take_order/finish_order','App\Http\Controllers\Api\Deliverer\TakeOrderController@finishOrder');
    $api->post('/take_order/complete_order','App\Http\Controllers\Api\User\TakeOrderController@completeOrder');
    $api->post('/take_order/user/cancel_order','App\Http\Controllers\Api\User\TakeOrderController@cancelOrder');
    $api->post('/take_order/deliverer/cancel_order','App\Http\Controllers\Api\Deliverer\TakeOrderController@cancelOrder');
    $api->post('/take_order/agree_cancel_order','App\Http\Controllers\Api\User\TakeOrderController@agreeCancelOrder');

    $api->get('/take_order/user/order','App\Http\Controllers\Api\User\TakeOrderController@getUserOrders');
    $api->get('/take_order/deliverer/order','App\Http\Controllers\Api\Deliverer\TakeOrderController@getDelivererOrders');
});
