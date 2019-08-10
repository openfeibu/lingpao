<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\UserAddressRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Models\User;

class UserAddressRepository extends BaseRepository implements UserAddressRepositoryInterface
{

    /**
     * Booting the repository.
     *
     * @return null
     */
    /*
    public function boot()
    {
        $this->fieldSearchable = config('model.user.user_address.search');
    }
    */

    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return config('model.user.user_address.model');
    }

    public function store($userAddress)
    {
        try {
            if($userAddress['is_default'] == 1){
                $this->model->where('user_id',$userAddress['user_id'])->update(['is_default' => 0]);
            }
            return $this->model->create($userAddress);
        } catch (Exception $e) {
            throw new \App\Exceptions\OutputServerMessageException('无法创建收货地址');
        }
    }
    public function getUserAddress($where)
    {
        return $this->model->where($where)->orderBy('is_default','desc')->first();
    }
    public function getUserAddresses($where)
    {
        return $this->model->where($where)->orderBy('is_default','desc')->orderBy('id','desc')->get();
    }
}
