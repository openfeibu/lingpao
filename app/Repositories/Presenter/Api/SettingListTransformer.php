<?php

namespace App\Repositories\Presenter\Api;

use League\Fractal\TransformerAbstract;

class SettingListTransformer extends TransformerAbstract
{
    public function transform(\App\Models\Setting $setting)
    {
        return [
            'title' => $setting->title,
            'slug' => $setting->slug,
            'value' => $setting->value,
        ];
    }
}
