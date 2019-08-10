<?php

namespace App\Repositories\Presenter;

use League\Fractal\TransformerAbstract;

class NavListTransformer extends TransformerAbstract
{
    public function transform(\App\Models\Nav $nav)
    {
        return [
            'id'      => $nav->id,
            'url'     => $nav->url,
            'name'    => $nav->name,
            'image'   => $nav->image ? url("/image/sm".$nav->image) : '',
            'order'   => $nav->order,
            'category_name' => $nav->category->name,
        ];
    }
}
