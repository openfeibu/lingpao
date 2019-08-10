<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ResourceController as BaseController;
use Form;
use App\Http\Requests\MenuRequest;
use App\Repositories\Eloquent\MenuRepositoryInterface;
use App\Models\Menu;
use Response;

class MenuResourceController extends BaseController
{

    /**
     * Initialize Menu controller.
     *
     * @param type MenuRepositoryInterface $menu
     *
     */
    public function __construct(MenuRepositoryInterface $menu)
    {
        parent::__construct();
        $this->repository = $menu;
    }

    /**
     * Display a listing of the resource.
     *
     * @param MenuRequest $request
     * @param int     $parent
     *
     * @return Response
     */
    public function index(MenuRequest $request, $parent = 1)
    {
        $parent   = $this->repository->find($parent);
        $rootMenu = $this->repository->rootMenues();

        $menus = $this->repository->scopeQuery(function ($query) {
            return $query->orderBy('order', 'ASC');
        })->all()->toMenu($parent->key);

        if ($this->response->typeIs('json')) {

        }

        return $this->response->title(trans('menu.names'))
            ->view('menu.index')
            ->data(compact('rootMenu', 'parent','menus'))
            ->output();
    }

    /**
     * Display the specified resource.
     *
     * @param MenuRequest $request
     * @param int     $parent
     *
     * @return Response
     */
    public function show(MenuRequest $request, $parent)
    {
        if ($request->ajax()) {
            $menu = $parent;

            Form::populate($menu);

            return view('menu::admin.show', compact('menu'));
        }

        $rootMenu = $this->repository->rootMenues();
        return $this->response->title(trans('menu::menu.names'))
            ->view('menu::index', true)
            ->data(compact('rootMenu', 'parent'))
            ->output();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param MenuRequest $request
     *
     * @return Response
     */
    public function create(MenuRequest $request, Menu $menu)
    {
        $menu = $this->repository->newInstance([]);

        Form::populate($menu);

        return view('menu::admin.create', compact('menu'));
    }

    /**
     * Create the specified resource.
     *
     * @param MenuRequest $request
     *
     * @return Response
     */
    public function store(MenuRequest $request)
    {
        try {
            $attributes            = $request->all();
            $menu                  = $this->repository->create($attributes);

            return $this->response
                ->message(trans('messages.success.created', ['Module' => trans('menu::menu.name')]))
                ->http_code(204)
                ->status('success')
                ->url(guard_url('menu/menu/' . $menu->getRouteKey()))
                ->redirect();

        } catch (Exception $e) {
            return $this->response
                ->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('menu/menu'))
                ->redirect();
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param MenuRequest $request
     * @param Menu $menu
     *
     * @return Response
     */
    public function edit(MenuRequest $request, $menu)
    {
        $data['menu'] = $menu;
        Form::populate($data['menu']);

        return view('menu::admin.edit', $data);
    }

    /**
     * Update the specified resource.
     *
     * @param MenuRequest $request
     * @param Menu $menu
     *
     * @return Response
     */
    public function update(MenuRequest $request, $menu)
    {
        try {

            $attributes = $request->all();

            $menu->update($attributes);

            return $this->response->message(trans('messages.success.updated', ['Module' => trans('menu::menu.name')]))
                ->http_code(204)
                ->status('success')
                ->url(guard_url('menu/menu/' . $menu->getRouteKey()))
                ->redirect();
        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('menu/menu/' . $menu->getRouteKey()))
                ->redirect();

        }

    }

    /**
     * Remove the specified resource.
     *
     * @param MenuRequest $request
     * @param Menu $menu
     *
     * @return Response
     */
    public function destroy(MenuRequest $request, Menu $menu)
    {
        $cid = $menu->id;

        if ($this->repository->findByField('parent_id', $cid)->count() > 0) {
            return response()->json([
                'message' => 'Child menu exists.',
                'type'    => 'warning',
                'title'   => 'Warning',
            ], 409);
        }

        try {

            $menu->forceDelete();
            return $this->response->message(trans('messages.success.deleted', ['Module' => trans('menu::menu.name')]))
                ->http_code(202)
                ->status('success')
                ->url(guard_url('menu/menu'))
                ->redirect();

        } catch (Exception $e) {

            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('menu/menu/' . $menu->getRouteKey()))
                ->redirect();
        }

    }

    /**
     * Update tree structure  of the menu.
     *
     * @param MenuRequest $request
     * @param type $id
     *
     * @return type
     */
    public function tree(MenuRequest $request, $id)
    {
        $this->repository->updateTree($id, $request->get('tree'));
    }

    /**
     * Display a listing of the resource.
     *
     * @param MenuRequest $request
     * @param int $parent
     *
     * @return Response
     */
    public function nested(MenuRequest $request, $parent = 1)
    {
        $parent = $this->repository->all();
    }

}
