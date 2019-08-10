<?php

namespace App;

use Teepluss\Theme\Theme AS BaseTheme;
use Illuminate\View\Factory;
use Illuminate\Config\Repository;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Cookie;

class Theme extends BaseTheme
{
    /**
     * Theme namespace.
     */
    public static $namespace = 'theme';

    /**
     * Repository config.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Event dispatcher.
     *
     * @var \Illuminate\Events\Dispatcher
     */
    protected $events;

    /**
     * Theme configuration.
     *
     * @var mixed
     */
    protected $themeConfig;

    /**
     * View.
     *
     * @var \Illuminate\View\Factory
     */
    protected $view;

    /**
     * Asset.
     *
     * @var \Teepluss\Assets
     */
    protected $asset;

    /**
     * Filesystem.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Breadcrumb.
     *
     * @var \Teepluss\Breadcrumb
     */
    protected $breadcrumb;

    /**
     * The name of theme.
     *
     * @var string
     */
    protected $theme;

    /**
     * The name of layout.
     *
     * @var string
     */
    protected $layout;

    /**
     * Content dot path.
     *
     * @var string
     */
    protected $content;

    /**
     * Regions in the theme.
     *
     * @var array
     */
    protected $regions = array();

    /**
     * Content arguments.
     *
     * @var array
     */
    protected $arguments = array();

    /**
     * Data bindings.
     *
     * @var array
     */
    protected $bindings = array();

    /**
     * Cookie var.
     *
     * @var Cookie
     */
    protected $cookie;

    /**
     * Engine compiler.
     *
     * @var array
     */
    protected $compilers = array();


    public function __construct($config,$events,$view,$asset,$files,$breadcrumb)
    {
        parent::__construct($config,$events,$view,$asset,$files,$breadcrumb);
    }


    /**
     * Get theme config.
     *
     * @param  string $key
     * @return mixed
     */
    public function getConfig($key = null)
    {

        // Main package config.
        if (!$this->themeConfig) {
            $this->themeConfig = config('theme');
        }

        // Config inside a public theme.

        // This config having buffer by array object.
        if ($this->theme and !isset($this->themeConfig['_themes'][$this->theme])) {
            $this->themeConfig['_themes'][$this->theme] = [];

            try {
                // Require public theme config.
                $minorConfigPath = public_path($this->themeConfig['themeDir'] . '/' . $this->theme . '/config.php');

                $this->themeConfig['_themes'][$this->theme] = $this->files->getRequire($minorConfigPath);
            } catch (\Illuminate\Filesystem\FileNotFoundException $e) {
                //var_dump($e->getMessage());
            }

        }

        // Evaluate theme config.
        $this->themeConfig = $this->evaluateConfig($this->themeConfig);

        return is_null($key) ? $this->themeConfig : array_get($this->themeConfig, $key);
    }

    /**
     * Evaluate config.
     *
     * Config minor is at public folder [theme]/config.php,
     * thet can be override package config.
     *
     * @param  mixed $config
     * @return mixed
     */
    protected function evaluateConfig($config)
    {

        if (!isset($config['_themes'][$this->theme])) {
            return $config;
        }

        // Config inside a public theme.
        $minorConfig = $config['_themes'][$this->theme];

// Before event is special case, It's combination.
        if (isset($minorConfig['events']['before'])) {
            $minorConfig['events']['appendBefore'] = $minorConfig['events']['before'];
            unset($minorConfig['events']['before']);
        }

        // Merge two config into one.
        $config = array_replace_recursive($config, $minorConfig);

        // Reset theme config.
        $config['_themes'][$this->theme] = [];

        return $config;
    }
}
