<?php

namespace App\Repositories\Presenter\Api;

use League\Fractal\TransformerAbstract;

class RecruitListTransformer extends TransformerAbstract
{
    public function transform(\App\Models\Recruit $recruit)
    {
        return [
            'id' => $recruit->id,
            'title' => $recruit->title,
            'address' => $recruit->address,
            'salary' => $recruit->salary,
            'image' => $recruit->image ? url("/image/original".$recruit->image) : '',
            'requirement' => $recruit->requirement,
            'duty' => $recruit->duty,
        ];
    }
}
