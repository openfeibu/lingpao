<?php

namespace App\Repositories\Presenter;

use App\Repositories\Presenter\FractalPresenter;

class BannerListPresenter extends FractalPresenter
{

    /**
     * Prepare data to present
     *
     * @return \App\Repositories\Eloquent\BannerListTransformer
     */
    public function getTransformer()
    {
        return new BannerListTransformer();
    }
}
