<?php

return [

/*
 * Provider .
 */
    'provider' => 'litecms',

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

// Modale variables for page module.
    'page'     => [
        'model'        => 'App\Models\Page',
        'table'        => 'pages',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        'slugs'        => ['slug' => 'name'],
        'dates'        => ['deleted_at'],
        'fillable'     => ['heading', 'meta_title', 'meta_keyword','name','title', 'category_id','slug', 'order', 'view', 'compile', 'status',
                           'upload_folder', 'image','description','content',  'abstract','recommend_type'],
        'translate'    => ['name', 'heading', 'content', 'meta_title', 'meta_keyword', 'meta_description'],
        'upload_folder' => '/page/page',
        'uploads'      => [
            'banner' => [
                'count' => 1,
                'type'  => 'image',
            ],
            'images' => [
                'count' => 10,
                'type'  => 'image',
            ],
        ],
        'casts'        => [
            'banner' => 'array',
            'images' => 'array',
        ],
        'encrypt'      => ['id'],
        'revision'     => ['name', 'title'],
        'perPage'      => '20',
        'search'        => [
            'name'  => 'like',
            'title'  => 'like',
            'heading'  => 'like',
            'slug'  => 'like',
            'order'  => 'like'
        ],
    ],
    'category' => [
        'model'        => 'App\Models\PageCategory',
        'table'        => 'page_categories',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        'slugs'        => ['slug' => 'slug'],
        'fillable'     => [ 'name', 'slug', 'order','parent_id'],
        'encrypt'      => ['id'],
        'perPage'      => '20',
        'search'        => [
            'name'  => 'like',
            'slug'  => 'like',
        ],
    ],
    'recruit' => [
        'model'        => 'App\Models\Recruit',
        'table'        => 'recruits',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        'upload_folder' => '/page/recruit',
        'fillable'     => [ 'title', 'address', 'salary','requirement','duty','order','image'],
        'encrypt'      => ['id'],
        'perPage'      => '20',
        'search'        => [
            'title'  => 'like',
        ],
    ],
];
