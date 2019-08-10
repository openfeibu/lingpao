<?php

namespace App\Http\Controllers\Api;

use App\Repositories\Eloquent\LinkRepositoryInterface;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use App\Models\Page;

class LinkController extends BaseController
{
    public function __construct(LinkRepositoryInterface $link)
    {
        parent::__construct();
        $this->repository = $link;
    }
    public function getLinks(Request $request)
    {
        $links = $this->repository
            ->where(['category' => 'partners'])
            ->orderBy('order','asc')
            ->orderBy('id','asc')
            ->setPresenter(\App\Repositories\Presenter\Api\LinkListPresenter::class)
            ->all();
        return [
            'code' => '200',
            'meta_title' => '',
            'meta_keyword' => '',
            'meta_description' => '',
            'data' => $links['data'],
        ];
    }
}