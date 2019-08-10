<?php

namespace App\Repositories\Presenter\Api;

use League\Fractal\TransformerAbstract;

class LinkListTransformer extends TransformerAbstract
{
    public function transform(\App\Models\Link $link)
    {
        return [
            'id' => $link->id,
            'name' => $link->name,
            'image' => $link->image ? url("/image/original".$link->image) : '',
        ];
    }
}
