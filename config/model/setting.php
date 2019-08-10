<?php

return [

/*
 * Modules .
 */
    'modules'  => ['setting'],


/*
 * Views for the page  .
 */
    'views'    => ['default' => 'Default', 'left' => 'Left menu', 'right' => 'Right menu'],

// Modale variables for page module.
    'setting'     => [
        'model'        => 'App\Models\Setting',
        'table'        => 'settings',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        'slugs'        => ['slug' => 'name'],
        'fillable'     => ['title', 'slug', 'order', 'value', 'type','category'],
        'translate'    => ['title', 'slug', 'order', 'value', 'type','category'],
        'upload_folder' => '/page/page',
        'encrypt'      => ['id'],
        'revision'     => ['name', 'title'],
        'perPage'      => '20',
        'search'        => [
            'value'  => 'like',
            'title'  => 'like',
            'slug'  => 'like',
            'value' => 'like',
            'order'  => 'like'
        ],
    ],

];
