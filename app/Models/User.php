<?php

namespace App\Models;

use App\Exceptions\PermissionDeniedException;
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

    protected $appends = ['is_pay_password','role_name'];

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
    public static function isLogin()
    {
        $token = Request::input('token','');
        if(!$token)
        {
            return false;
        }
        $user_id = self::where('token', $token)->value('id');
        if(!$user_id)
        {
            return false;
        }
        return $user_id;
    }
    public static function getUserByToken($token,$custom=['*'])
    {
        return self::where('token', $token)->first($custom);
    }
    public static function getUserById($id,$custom=['*'])
    {
        return self::where('id', $id)->first($custom);
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
    public static function IsDeliverer()
    {
        $user_role = self::$user->role;
        if($user_role != 'deliverer' && $user_role != 'expert_deliverer')
        {
            throw new PermissionDeniedException("请先在个人中心申请骑手认证");
        }
    }
    public static function IsExpertDeliverer()
    {
        $user_role = self::$user->role;
        if($user_role != 'expert_deliverer')
        {
            throw new PermissionDeniedException("请联系客服申请骑士认证");
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
    public function getRoleNameAttribute()
    {
        $role_name = isset($this->attributes['role']) ? trans('user.roles.'.$this->attributes['role']) : '' ;
        return $role_name;
    }
}