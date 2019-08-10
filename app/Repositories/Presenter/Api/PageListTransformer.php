<?php

namespace App\Repositories\Presenter\Api;

use League\Fractal\TransformerAbstract;

class PageListTransformer extends TransformerAbstract
{
    public function transform(\App\Models\Page $page)
    {
        return [
            'id' => $page->id,
            'url' => url('/page',$page->id),
            'name' => $page->name,
            'heading' => $page->heading,
            'title' => $page->title,
            'image' => $page->image ? config('app.image_url').'/image/sm'.$page->image : '',
            //'images' => $page->images,
            'description' => $page->description ? $page->description : get_substr(strip_tags($page->content),200),
            'category_id' => $page->category_id,
            'date' => $page->updated_at->format('Y-m-d'),
            'views_count' => $page->views_count,
        ];
    }
}
