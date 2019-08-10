<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\PublicController as BaseController;

class PagePublicController extends BaseController
{
    /**
     * Constructor.
     *
     * @param \App\Repositories\Eloquent\PageRepositoryInterface $page
     *
     */
    public function __construct(\App\Repositories\Eloquent\PageRepositoryInterface $page)
    {
        parent::__construct();
        $this->model = $page;
    }

    /**
     * Show page.
     *
     * @param string $slug
     *
     * @return response
     */
    protected function getPage($slug)
    {
        // get page by slug
        $page = $this->model->getPage($slug);

        if (is_null($page)) {
            abort(404);
        }

        //Set theme variables
        $view = $page->view;
        $view = view()->exists('page::public.' . $view) ? $view : 'default';

        if ($page->compile) {
            $page->content = blade_compile($page->content);
        }

        return $this->response
            ->setMetaKeyword(strip_tags($page->meta_keyword))
            ->setMetaDescription(strip_tags($page->meta_description))
            ->setTitle(strip_tags($page->meta_title))
            ->view('page::public.' . $view)
            ->data(compact('page'))
            ->output();

    }

}
