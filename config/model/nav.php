<?php
return [
    /*
     * Package .
     */
    'package'  => 'page',

    /*
     * Modules .
     */
    'modules'  => ['page', 'category'],


    /*
     * Views for the page  .
     */
    'views'    => ['default' => 'Default', 'left' => 'Left menu', 'right' => 'Right menu'],

    'nav'     => [
        'model'        => 'App\Models\Nav',
        'table'        => 'navs',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        'dates'        => ['deleted_at'],
        'fillable'     => ['category_id','name','image','url', 'order'],
        'translate'    => ['category_id','name','image','url', 'order'],
        'upload_folder' => '/page/page',
        'encrypt'      => ['id'],
        'revision'     => ['name'],
        'perPage'      => '20',
        'search'        => [
            'name'  => 'like',
            'url'  => 'like',
            'order'  => 'like'
        ],
    ],
    'category' => [
        'model'        => 'App\Models\NavCategory',
        'table'        => 'nav_categories',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        'slugs'        => ['slug' => 'slug'],
        'fillable'     => [ 'name', 'slug', 'order'],
        'encrypt'      => ['id'],
        'perPage'      => '20',
        'search'        => [
            'name'  => 'like',
            'slug'  => 'like',
        ],
    ],
];