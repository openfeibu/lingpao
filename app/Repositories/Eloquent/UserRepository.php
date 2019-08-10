<?php

namespace App\Repositories\Eloquent;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use App\Repositories\Eloquent\UserRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Models\User;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{

    /**
     * Booting the repository.
     *
     * @return null
     */
    /*
    public function boot()
    {
        $this->fieldSearchable = config('model.user.user.search');
    }
    */

    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return config('model.user.user.model');
    }

    /**
     * 检验用户是否已经实名认证
     */
    public function isCurrentUserRealNameAuth()
    {
        $user = User::tokenAuth();
        if ($user->userInfo->realname) {
            throw new \App\Exceptions\OutputServerMessageException('你已经实名了');
        } elseif ($user->realnameAuth) {
            throw new \App\Exceptions\OutputServerMessageException('你已提交实名请求。');
        }
        return true;
    }
    public function updatePayPassword ($user_id,$pay_password)
    {
        return User::where('id',$user_id)->update(['pay_password'=>$pay_password]);
    }
    public function getUserByToken($token,$custom = ['*'])
    {
        return User::where('token',$token)->first($custom);
    }
}
