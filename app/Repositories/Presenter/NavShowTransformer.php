<?php

namespace App\Repositories\Presenter;

use League\Fractal\TransformerAbstract;
use Hashids;

class NavShowTransformer extends TransformerAbstract
{
    public function transform(\App\Models\Nav $nav)
    {
        return [
            'id'      => $nav->id,
            'category_id' => $nav->category_id,
            'url'     => $nav->url,
            'name'    => $nav->name,
            'image'   => $nav->image ? url("/image/sm".$nav->image) : '',
            'order'   => $nav->order,
        ];
    }
}