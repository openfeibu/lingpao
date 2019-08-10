<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ResourceController as BaseController;
use Illuminate\Http\Request;
use App\Models\Nav;
use App\Repositories\Eloquent\NavRepositoryInterface;
use App\Repositories\Eloquent\NavCategoryRepositoryInterface;

/**
 * Resource controller class for page.
 */
class NavResourceController extends BaseController
{

    /**
     * Initialize page resource controller.
     *
     * @param type NavRepositoryInterface $nav
     *
     */
    public function __construct(NavRepositoryInterface $nav,
                                NavCategoryRepositoryInterface $category_repository)
    {
        parent::__construct();
        $this->repository = $nav;
        $this->category_repository = $category_repository;
        $this->repository
            ->pushCriteria(\App\Repositories\Criteria\RequestCriteria::class)
            ->pushCriteria(\App\Repositories\Criteria\PageResourceCriteria::class);
    }

    /**
     * Display a list of page.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit',config('app.limit'));
        $data = $this->repository
            ->setPresenter(\App\Repositories\Presenter\NavListPresenter::class)
            ->getDataTable($limit);

        if ($this->response->typeIs('json')) {
            return $this->response
                ->success()
                ->count($data['recordsTotal'])
                ->data($data['data'])
                ->output();
        }
        $pages = $this->repository->paginate();
        return $this->response->title(trans('nav.names'))
            ->view('nav.index', true)
            ->data(compact('pages','data'))
            ->output();
    }

    /**
     * Display page.
     *
     * @param Request $request
     * @param Nav   $nav
     *
     * @return Response
     */
    public function show(Request $request, Nav $nav)
    {
        if ($nav->exists) {
            $view = 'nav.show';
        } else {
            $view = 'nav.new';
        }
        $categories = $this->category_repository->all(['id','name'])->toArray();
        return $this->response->title(trans('app.view') . ' ' . trans('nav.name'))
            ->data(compact('nav','categories'))
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
    public function create(Request $request)
    {

        $nav = $this->repository->newInstance([]);
        $categories = $this->category_repository->all(['id','name'])->toArray();
        return $this->response->title(trans('app.new') . ' ' . trans('nav.name'))
            ->view('nav.create', true)
            ->data(compact('nav','categories'))
            ->output();
    }

    /**
     * Create new page.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        try {
            $attributes = $request->all();
            $nav = $this->repository->create($attributes);

            return $this->response->message(trans('messages.success.created', ['Module' => trans('nav.name')]))
                ->http_code(204)
                ->status('success')
                ->url(guard_url('nav/nav/'))
                ->redirect();
        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('nav/nav'))
                ->redirect();
        }

    }

    /**
     * Show page for editing.
     *
     * @param Request $request
     * @param Nav   $nav
     *
     * @return Response
     */
    public function edit(Request $request, Nav $nav)
    {
        return $this->response->title(trans('app.edit') . ' ' . trans('nav.name'))
            ->view('admin.page.edit')
            ->data(compact('nav'))
            ->output();
    }

    /**
     * Update the page.
     *
     * @param Request $request
     * @param Nav   $nav
     *
     * @return Response
     */
    public function update(Request $request, Nav $nav)
    {
        try {
            $attributes = $request->all();

            $nav->update($attributes);
            return $this->response->message(trans('messages.success.updated', ['Module' => trans('nav.name')]))
                ->http_code(204)
                ->status('success')
                ->url(guard_url('nav/nav/' ))
                ->redirect();
        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('nav/nav/' ))
                ->redirect();
        }

    }

    /**
     * Remove the page.
     *
     * @param Request $request
     * @param Nav   $nav
     *
     * @return Response
     */
    public function destroy(Request $request,Nav $nav)
    {
        try {
            $this->repository->forceDelete([$nav->id]);

            return $this->response->message(trans('messages.success.deleted', ['Module' => trans('nav.name')]))
                ->status("success")
                ->http_code(202)
                ->url(guard_url('nav/nav'))
                ->redirect();

        } catch (Exception $e) {

            return $this->response->message($e->getMessage())
                ->status("error")
                ->code(400)
                ->url(guard_url('nav/nav'))
                ->redirect();
        }
    }
    public function destroyAll(Request $request)
    {
        try {
            $data = $request->all();
            $ids = $data['ids'];
            $this->repository->forceDelete($ids);

            return $this->response->message(trans('messages.success.deleted', ['Module' => trans('nav.name')]))
                ->status("success")
                ->http_code(202)
                ->url(guard_url('nav/nav'))
                ->redirect();

        } catch (Exception $e) {

            return $this->response->message($e->getMessage())
                ->status("error")
                ->code(400)
                ->url(guard_url('nav/nav'))
                ->redirect();
        }
    }
}