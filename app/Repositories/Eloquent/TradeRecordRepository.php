<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\TradeRecordRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Models\User;

class TradeRecordRepository extends BaseRepository implements TradeRecordRepositoryInterface
{

    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return config('model.trade_record.trade_record.model');
    }

}
