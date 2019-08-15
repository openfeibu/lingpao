<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use App\Models\UserAddress;
use DB;
use Illuminate\Http\Request;
use App\Repositories\Eloquent\UserAddressRepositoryInterface;

class UserAddressController extends BaseController
{
	protected $user;
	
	public function __construct (UserAddressRepositoryInterface $userAddressRepository)
	{
		parent::__construct();
		$this->middleware('auth.api');
		$this->userAddressRepository = $userAddressRepository;
	 	$this->user = User::tokenAuth();
	}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     
    public function getUserAddresses()
    {
       	$rules = [
        	'token' 	=> 'required',
        ];
        validateParameter($rules);
        $user_addresses = $this->userAddressRepository->getUserAddresses(['user_id' => $this->user->id])->toArray();
        return $this->response->success()->data($user_addresses)->json();
    }

    public function store(Request $request)
    {
        $rules = [
        	'token' 	=> 'required',
        	'consignee' => "required|string",
        	'address' => "required|string",
        	'mobile' => "required|regex:/^1[3456789][0-9]{9}/",
        	'is_default' => 'sometimes|required|in:0,1',
    	];
    	validateParameter($rules);

    	$userAddress = [
    		'user_id' => $this->user->id,
			'consignee' => $request->consignee,
			'address' => $request->address,
			'mobile' => $request->mobile,
			'is_default' => isset($request->is_default) ? $request->is_default : 0,
    	];
    	$user_address = $this->userAddressRepository->store($userAddress);

        return $this->response->success('创建成功')->data($user_address)->json();

    }

    public function getUserAddress(Request $request,$id)
    {
        $rules = [
        	'token' 	=> 'required',
        ];
        validateParameter($rules);
        $user_address = $this->userAddressRepository->getUserAddress(['id' => $id,'user_id' => $this->user->id])->toArray();
        if(!$user_address){
			throw new \App\Exceptions\DataNotFoundException('收货地址不存在');
		}

        return $this->response->success()->data($user_address)->json();

    }
	public function getDefault(Request $request)
    {
        $rules = [
        	'token' 	=> 'required',
        ];
        validateParameter($rules);
        $user_address = $this->userAddressRepository->getUserAddress(['user_id' => $this->user->id]);

        return $this->response->success()->data($user_address->toArray())->json();

    }

    public function update(Request $request)
    {
        $rules = [
        	'token' 	=> 'required',
        	'address_id' => "required|integer",
        	'consignee' => "required|string",
        	'address' => "required|string",
        	'mobile' => "required|regex:/^1[34578][0-9]{9}/",
        	'is_default' => 'required|in:0,1',
    	];
    	validateParameter($rules);
		if($request->is_default == 1){
	    	UserAddress::where('user_id', $this->user->id)->update(['is_default' => 0]);
    	}
    	$userAddress = [
			'consignee' => $request->consignee,
			'address' => $request->address,
			'mobile' => $request->mobile,
			'is_default' => $request->is_default,
    	];
    	$where = ['id' => $request->address_id,'user_id' => $this->user->id];
        UserAddress::where($where)->update($userAddress);
    	throw new \App\Exceptions\RequestSuccessException("更新成功");
    }
	

    public function destroy(Request $request)
    {
        $rules = [
        	'token' 		=> 'required',
        	'address_id' 		=> 'required|integer',
    	];
    	validateParameter($rules);
    	$where = ['address_id' => $request->address_id,'user_id' => $this->user->id ];
    	$this->userAddressRepository->deleteWhere($where);
    	throw new \App\Exceptions\RequestSuccessException('删除成功');
    }
}
