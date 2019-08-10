<?php

namespace App\Repositories\Presenter;

use Route,Auth;
use App\Repositories\Presenter\FractalPresenter;
use App\Repositories\Eloquent\PermissionRepositoryInterface;

/**
 * Class PermissionPresenter
 *
 * @package namespace App\Repositories\Presenters;
 */
class PermissionPresenter extends FractalPresenter
{
    protected $permission;

    public function __construct(PermissionRepositoryInterface $permission)
    {

        $this->permission = $permission;

    }

    public function getTransformer()
    {
        return new PermissionTransformer();
    }
    /**
     * 用户根据权限可见的菜单
     * @return string
     */
    public function menus()
    {

        $menus = $this->permission->menus();

        $html = '';
        if($menus) {

            foreach ($menus as $menu) {

                if(($menu->slug !== '#') && !Route::has($menu->slug)) {
                    continue;
                }

                $class = '';

                if($menu->active) {
                    $class .= 'layui-nav-itemed';
                }

                $html .= '<li class="layui-nav-item '.$class.'">';
                $href = ($menu->slug == '#') || isset($menu->sub)  ? 'javascript:;' : route($menu->slug);
                $html .= sprintf('<a href="%s">%s %s</a>', $href, $menu->icon_html, $menu->name);

                if(!isset($menu->sub)) {
                    $html .= '</li>';
                    continue;
                }

                $html .= '<dl class="layui-nav-child">';

                foreach ($menu->sub as $sub) {
                    if(($sub->slug !== '#') && !Route::has($sub->slug)) {
                        continue;
                    }
                    $href = ($sub->slug == '#') ? '#' : route($sub->slug);
                    $icon = $sub->icon_html ? $sub->icon_html : '';

                    $class = $sub->active ? 'layui-this' : '' ;

                    $html .= sprintf('<dd class="'.$class.'"><a href="%s">%s %s</a></dd>', $href, $icon, $sub->name);

                }
                $html .= '</dl>';

                $html .= '</li>';
            }
        }

        return $html;
    }
}