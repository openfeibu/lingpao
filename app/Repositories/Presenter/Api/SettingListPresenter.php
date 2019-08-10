<?php

namespace App\Repositories\Presenter\Api;

use App\Repositories\Presenter\FractalPresenter;

class SettingListPresenter extends FractalPresenter
{

    /**
     * Prepare data to present
     *
     * @return \App\Repositories\Eloquent\Api\SettingListTransformer
     */
    public function getTransformer()
    {
        return new SettingListTransformer();
    }
}
