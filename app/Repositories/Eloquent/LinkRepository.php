<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\LinkRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class LinkRepository extends BaseRepository implements LinkRepositoryInterface
{
    public function model()
    {
        return config('model.link.link.model');
    }
}