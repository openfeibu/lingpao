<?php

namespace App\Repositories\Presenter;

use League\Fractal\TransformerAbstract;

class BannerListTransformer extends TransformerAbstract
{
    public function transform(\App\Models\Banner $banner)
    {
        return [
            'id' => $banner->id,
            'image' => $banner->image ? url("/image/original".$banner->image) : '',
            'url' => $banner->url,
            'order' => $banner->order,
        ];
    }
}
