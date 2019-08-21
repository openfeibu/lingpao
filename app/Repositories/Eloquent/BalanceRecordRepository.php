<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\BalanceRecordRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Models\User;
use Request;

class BalanceRecordRepository extends BaseRepository implements BalanceRecordRepositoryInterface
{

    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return config('model.balance_record.balance_record.model');
    }
    public function getBalanceRecords($user_id)
    {
        $limit = Request::get('limit',config('app.limit'));
        $balance_records = $this->where('user_id',$user_id)->orderBy('id','desc')->paginate($limit,[
            '*'
        ]);

        return $balance_records;
    }
}
