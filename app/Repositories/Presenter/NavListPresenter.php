<?php

namespace App\Repositories\Presenter;

use App\Repositories\Presenter\FractalPresenter;

class NavListPresenter extends FractalPresenter
{

    /**
     * Prepare data to present
     *
     * @return \App\Repositories\Eloquent\NavListTransformer
     */
    public function getTransformer()
    {
        return new NavListTransformer();
    }
}
