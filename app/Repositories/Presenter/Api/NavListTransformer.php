<?php

namespace App\Repositories\Presenter\Api;

use League\Fractal\TransformerAbstract;

class NavListTransformer extends TransformerAbstract
{
    public function transform(\App\Models\Nav $nav)
    {
        return [
            'id' => $nav->id,
            'category_id' => $nav->category_id,
            'name' => $nav->name,
            'url' => $nav->url,
            'image' => $nav->image ? url("/image/original".$nav->image) : '',
        ];
    }
}
