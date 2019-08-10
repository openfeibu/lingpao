<?php

namespace App\Repositories\Presenter\Api;

use App\Repositories\Presenter\FractalPresenter;

class PageListPresenter extends FractalPresenter
{

    /**
     * Prepare data to present
     *
     * @return \App\Repositories\Eloquent\Api\PageListTransformer
     */
    public function getTransformer()
    {
        return new PageListTransformer();
    }
}
