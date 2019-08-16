<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\BalanceRecordRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Models\User;

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

}
