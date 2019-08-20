<?php

namespace App\Models;

use App\Exceptions\Roles\PermissionDeniedException;
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

    protected $appends = ['is_pay_password'];

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
    /*
     * $role :common,deliverer,expert_deliverer
     */
    public static function isRole($role='deliverer')
    {
        $user_role = self::$user->role;
        if($user_role != $role)
        {
            throw new PermissionDeniedException("请先在个人中心申请骑手认证");
        }
    }

    public function deliverer()
    {
        return true;
    }
    public function getIsPayPasswordAttribute()
    {
        $pay_password = $this->attributes['pay_password'];
        return $pay_password ? true : false;
    }
}