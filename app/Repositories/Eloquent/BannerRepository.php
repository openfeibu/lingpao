<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\BannerRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class BannerRepository extends BaseRepository implements BannerRepositoryInterface
{
    public function model()
    {
        return config('model.banner.banner.model');
    }
}