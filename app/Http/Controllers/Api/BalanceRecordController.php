<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use App\Models\BalanceRecord;
use DB;
use Illuminate\Http\Request;
use App\Repositories\Eloquent\BalanceRecordRepositoryInterface;

class BalanceRecordController extends BaseController
{
	protected $user;
	
	public function __construct (BalanceRecordRepositoryInterface $balanceRecordRepository)
	{
		parent::__construct();
		$this->middleware('auth.api');
		$this->balanceRecordRepository = $balanceRecordRepository;
	 	$this->user = User::tokenAuth();
	}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     
    public function getBalanceRecords()
    {
        $balance_records = $this->balanceRecordRepository->getBalanceRecords($this->user->id);
        return $this->response->success()->count($balance_records->total())->data($balance_records->toArray()['data'])->json();
    }

}
