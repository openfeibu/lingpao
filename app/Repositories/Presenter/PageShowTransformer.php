<?php

namespace App\Repositories\Presenter;

use League\Fractal\TransformerAbstract;
use Hashids;

class PageShowTransformer extends TransformerAbstract
{
    public function transform(\App\Models\Page $page)
    {
        return [
            'id'      => $page->eid,
            'name'   => $page->name,
            'heading'   => $page->heading,
            'title'   => $page->title,
            'keyword'   => $page->keyword,
            'description'   => $page->description,
            'content' => $page->content,
            'abstract' => $page->abstract,
            'images' => $page->images,
            'created' => $page->created_at,
            'order' => $page->order
        ];
    }
}