<?php

namespace App\Repositories\Presenter;

use Litepie\Repository\Presenter\FractalPresenter;

class NavShowPresenter extends FractalPresenter
{

    /**
     * Prepare data to present
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return new NavShowTransformer();
    }
}
