<?php

namespace App\Repositories\Presenter;

use App\Repositories\Presenter\FractalPresenter;

class PageCategoryListPresenter extends FractalPresenter
{

    /**
     * Prepare data to present
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return new PageCategoryListTransformer();
    }
}
