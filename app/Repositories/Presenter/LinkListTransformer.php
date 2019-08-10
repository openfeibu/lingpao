<?php

namespace App\Repositories\Presenter;

use League\Fractal\TransformerAbstract;

class LinkListTransformer extends TransformerAbstract
{
    public function transform(\App\Models\Link $link)
    {
        return [
            'id' => $link->id,
            'name' => $link->name,
            'order' => $link->order,
            'image' => $link->image ? url("/image/sm".$link->image) : '',
        ];
    }
}
