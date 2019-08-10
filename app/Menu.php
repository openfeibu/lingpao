<?php

namespace App;
use App\Repositories\Eloquent\MenuRepositoryInterface;
use App\Http\Response\ResourceResponse;
use App\Traits\Theme\ThemeAndViews;
use App\Traits\AdminUser\RoutesAndGuards;

class Menu
{
    use ThemeAndViews,RoutesAndGuards;

    protected $model;

    public $response;

    public function __construct(MenuRepositoryInterface $menu)
    {
        $this->model = $menu;
        $this->response = app(ResourceResponse::class);
        $this->setTheme();
    }

    public function model()
    {
        return $this->model->getModel();
    }

    public function menu($key, $view = null)
    {
        $menus = $this->model->scopeQuery(function ($query) {
        return $query->orderBy('order', 'ASC');
    })->all()->toMenu($key);

        if (is_null($view)) {
            $view = 'menu.menu.' . $key;
        }

        if (!view()->exists($view)) {
            $view = 'menu.menu.default';
        }
        return view($view, compact('menus'));
    }

    public function select($key, $view = 'menu::menu.default')
    {
        $menu = $this->model->getAllSubMenus($key);

        return view("menu.$view", compact('menu'));
    }

}
