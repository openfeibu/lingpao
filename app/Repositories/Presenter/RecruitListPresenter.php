<?php

namespace App\Repositories\Presenter;

use App\Repositories\Presenter\FractalPresenter;

class RecruitListPresenter extends FractalPresenter
{

    /**
     * Prepare data to present
     *
     * @return \App\Repositories\Eloquent\Api\RecruitListTransformer
     */
    public function getTransformer()
    {
        return new RecruitListTransformer();
    }
}
