<?php

namespace App\Repositories\Presenter\Api;

use App\Repositories\Presenter\FractalPresenter;

class RecruitListPresenter extends FractalPresenter
{

    /**
     * Prepare data to present
     *
     * @return \App\Repositories\Eloquent\Api\PageShowTransformer
     */
    public function getTransformer()
    {
        return new RecruitListTransformer();
    }
}
