<?php namespace App\Widgets;

use Tree;
use Teepluss\Theme\Theme;
use Teepluss\Theme\Widget;
use App\Models\NavCategory;

class Nav extends Widget {

    /**
     * Widget template.
     *
     * @var string
     */
    public $template = 'nav';

    /**
     * Watching widget tpl on everywhere.
     *
     * @var boolean
     */
    public $watch = false;

    /**
     * Arrtibutes pass from a widget.
     *
     * @var array
     */
    public $attributes = array();

    /**
     * Turn on/off widget.
     *
     * @var boolean
     */
    public $enable = true;

    /**
     * Code to start this widget.
     *
     * @return void
     */
    public function init(Theme $theme)
    {

    }

    /**
     * Logic given to a widget and pass to widget's view.
     *
     * @return array
     */
    public function run()
    {
        /*
        $navs = app('nav_repository')->allNavs()->toArray();

        $navs = Tree::getLevelTree($navs);
        */
        $category_slug = isset($this->attributes['slug']) && $this->attributes['slug'] ? $this->attributes['slug'] : 'top';

        $this->template = isset($this->attributes['template']) && $this->attributes['template'] ? $this->attributes['template'] : $this->template;
        $category = app(NavCategory::class)->where('slug',$category_slug)->first();

        $navs = app('nav_repository')->navs($category['id']);

        $this->setAttribute('navs',$navs);

        $attrs = $this->getAttributes();

        return $attrs;
    }

}