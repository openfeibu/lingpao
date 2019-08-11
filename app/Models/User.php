<?php

namespace App\Models;

use DB,Hash,Auth,Request;
use App\Models\Auth as AuthModel;
use App\Traits\Database\Slugger;
use App\Traits\Database\DateFormatter;
use App\Traits\Filer\Filer;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class User extends AuthModel
{
    use Filer, Slugger, DateFormatter;

    protected $config = 'model.user.user';

    protected static $user;

    public static function tokenAuthCache()
    {
        if (!self::$user) {
            self::$user = self::tokenAuth();
        }
        return self::$user;
    }

    public static function tokenAuth($custom = ['*'])
    {
        $token = Request::input('token','');
        self::$user = $user = self::where('token', $token)->first($custom);
        if (!$user) {
            throw new UnauthorizedHttpException('jwt-auth', 'token过期请重新登陆');
        }
        return $user;
    }
    public function findUserByToken()
    {

    }
}