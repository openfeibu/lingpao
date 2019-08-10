<?php

namespace App\Repositories\Presenter;

use League\Fractal\TransformerAbstract;

class PageCategoryListTransformer extends TransformerAbstract
{
    public function transform(\App\Models\PageCategory $category)
    {
        return [
            'id'      => $category->id,
            'slug'    => $category->slug,
            'name'    => $category->name,
            'order'    => $category->order,
            'parent_id'     => $category->parent_id,
        ];
    }
}
