<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Eloquent\DelivererIdentificationRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class DelivererIdentificationRepository extends BaseRepository implements DelivererIdentificationRepositoryInterface
{
    public function model()
    {
        return config('model.user.deliverer_identification.model');
    }
}