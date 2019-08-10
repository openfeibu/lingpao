<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ResourceController as BaseController;
use App\Http\Requests\PageRequest;
use App\Repositories\Eloquent\PageRepositoryInterface;
use App\Models\Page;

/**
 * Resource controller class for page.
 */
class PageResourceController extends BaseController
{

    /**
     * Initialize page resource controller.
     *
     * @param type PageRepositoryInterface $page
     *
     */
    public function __construct(PageRepositoryInterface $page)
    {
        parent::__construct();
        $this->repository = $page;
        $this->repository
            ->pushCriteria(\App\Repositories\Criteria\RequestCriteria::class)
            ->pushCriteria(\App\Repositories\Criteria\PageResourceCriteria::class);
    }

    /**
     * Display a list of page.
     *
     * @param PageRequest $request
     * @return Response
     */
    public function index(PageRequest $request)
    {
        $pageLimit = $request->input('limit');
        $data = $this->repository
            ->with(['category'])
            ->setPresenter(\App\Repositories\Presenter\PageListPresenter::class)
            ->getDataTable($pageLimit);

        if ($this->response->typeIs('json')) {
            return $this->response
                ->data($data)
                ->output();
        }
        $pages = $this->repository->paginate();
        return $this->response->title(trans('page.names'))
            ->view('page.index', true)
            ->data(compact('pages','data'))
            ->output();
    }

    /**
     * Display page.
     *
     * @param PageRequest $request
     * @param Page   $page
     *
     * @return Response
     */
    public function show(PageRequest $request, Page $page)
    {

        if ($page->exists) {
            $view = 'page.show';
        } else {
            $view = 'page.new';
        }

        return $this->response->title(trans('app.view') . ' ' . trans('page.name'))
            ->data(compact('page'))
            ->view($view)
            ->output();
    }

    /**
     * Show the form for creating a new page.
     *
     * @param PageRequest $request
     *
     * @return Response
     */
    public function create(PageRequest $request)
    {

        $page = $this->repository->newInstance([]);
        return $this->response->title(trans('app.new') . ' ' . trans('page.name')) 
            ->view('page.create', true) 
            ->data(compact('page'))
            ->output();
    }

    /**
     * Create new page.
     *
     * @param PageRequest $request
     *
     * @return Response
     */
    public function store(PageRequest $request)
    {
        try {
            $attributes              = $request->all();
            $attributes['user_id']   = user_id();
            $attributes['user_type'] = user_type();
//            $attributes['category_id'] = $this->category_id;

            $page                 = $this->repository->create($attributes);

            return $this->response->message(trans('messages.success.created', ['Module' => trans('page.name')]))
                ->http_code(204)
                ->status('success')
                ->url(guard_url('page/page/' . $page->getRouteKey()))
                ->redirect();
        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('page/page'))
                ->redirect();
        }

    }

    /**
     * Show page for editing.
     *
     * @param PageRequest $request
     * @param Page   $page
     *
     * @return Response
     */
    public function edit(PageRequest $request, Page $page)
    {
        return $this->response->title(trans('app.edit') . ' ' . trans('page.name'))
            ->view('admin.page.edit')
            ->data(compact('page'))
            ->output();
    }

    /**
     * Update the page.
     *
     * @param PageRequest $request
     * @param Page   $page
     *
     * @return Response
     */
    public function update(PageRequest $request, Page $page)
    {
        try {
            $attributes = $request->all();

            $page->update($attributes);
            return $this->response->message(trans('messages.success.updated', ['Module' => trans('page.name')]))
                ->http_code(204)
                ->status('success')
                ->url(guard_url('page/page/' . $page->getRouteKey()))
                ->redirect();
        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('page/page/' . $page->getRouteKey()))
                ->redirect();
        }

    }

    /**
     * Remove the page.
     *
     * @param PageRequest $request
     * @param Page   $page
     *
     * @return Response
     */
    public function destroy(PageRequest $request, Page $page)
    {
        try {

            $page->forceDelete();
            return $this->response->message(trans('messages.success.deleted', ['Module' => trans('page.name')]))
                ->http_code(202)
                ->status('success')
                ->url(guard_url('page/page/0'))
                ->redirect();

        } catch (Exception $e) {

            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('page/page/' . $page->getRouteKey()))
                ->redirect();
        }

    }

    /**
     * Remove multiple page.
     *
     * @param PageRequest $request
     * @param $type
     *
     * @return Response
     */
    public function delete(PageRequest $request, $type)
    {
        try {
            $ids = hashids_decode($request->input('ids'));

            if ($type == 'purge') {
                $this->repository->purge($ids);
            } else {
                $this->repository->forceDelete($ids);
            }

            return $this->response->message(trans('messages.success.deleted', ['Module' => trans('page.name')]))
                ->status("success")
                ->http_code(202)
                ->url(guard_url('page/page'))
                ->redirect();

        } catch (Exception $e) {

            return $this->response->message($e->getMessage())
                ->status("error")
                ->code(400)
                ->url(guard_url('page/page'))
                ->redirect();
        }

    }

    /**
     * Restore deleted pages.
     *
     * @param PageRequest   $request
     *
     * @return Response
     */
    public function restore(PageRequest $request)
    {
        try {
            $ids = hashids_decode($request->input('ids'));
            $this->repository->restore($ids);

            return $this->response->message(trans('messages.success.restore', ['Module' => trans('page.name')]))
                ->status("success")
                ->http_code(202)
                ->url(guard_url('page/page'))
                ->redirect();

        } catch (Exception $e) {

            return $this->response->message($e->getMessage())
                ->status("error")
                ->code(400)
                ->url(guard_url('page/page/'))
                ->redirect();
        }

    }

}