<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Response\ApiResponse;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class BaseController extends Controller
{
    use Helpers;

    public $response;

    public function __construct()
    {
        set_route_guard('api','user','user');
        $this->response = app(ApiResponse::class);
    }
}
