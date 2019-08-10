<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\PageRecruitRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class SettingRepository extends BaseRepository implements SettingRepositoryInterface
{
    public function model()
    {
        return config('model.setting.setting.model');
    }
}