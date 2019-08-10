<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\PageRecruitRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class PageRecruitRepository extends BaseRepository implements PageRecruitRepositoryInterface
{
    public function model()
    {
        return config('model.page.recruit.model');
    }
}