<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Response\ApiResponse;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Traits\Theme\ThemeAndViews;
use App\Traits\RoutesAndGuards;

class BaseController extends Controller
{
    use Helpers,ThemeAndViews,RoutesAndGuards;

    public $response;

    public function __construct()
    {
        set_route_guard('web','user','wap');
        $this->response = app(ApiResponse::class);
        $this->setTheme();
    }
}
