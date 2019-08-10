<?php

namespace App\Repositories\Presenter;

use App\Repositories\Presenter\FractalPresenter;

class PageListPresenter extends FractalPresenter
{

    /**
     * Prepare data to present
     *
     * @return \App\Repositories\Eloquent\PageListTransformer
     */
    public function getTransformer()
    {
        return new PageListTransformer();
    }
}
