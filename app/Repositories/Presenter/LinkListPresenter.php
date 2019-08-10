<?php

namespace App\Repositories\Presenter;

use App\Repositories\Presenter\FractalPresenter;

class LinkListPresenter extends FractalPresenter
{

    /**
     * Prepare data to present
     *
     * @return \App\Repositories\Eloquent\Presenter\LinkListTransformer
     */
    public function getTransformer()
    {
        return new LinkListTransformer();
    }
}
