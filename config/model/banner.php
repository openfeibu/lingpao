<?php

return [

/*
 * Modules .
 */
    'modules'  => ['banner'],


/*
 * Views for the page  .
 */
    'views'    => ['default' => 'Default', 'left' => 'Left menu', 'right' => 'Right menu'],

// Modale variables for page module.
    'banner'     => [
        'model'        => 'App\Models\Banner',
        'table'        => 'banners',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        //'slugs'        => ['slug' => 'name'],
        'fillable'     => ['title','image', 'url','order','type'],
        'translate'    => ['title','image', 'url'],
        'upload_folder' => '/banner',
        'encrypt'      => ['id'],
        'revision'     => ['title'],
        'perPage'      => '20',
        'search'        => [
            'title' => 'like',
            'url'  => 'like',
        ],
    ],

];
