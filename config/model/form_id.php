<?php

return [

/*
 * Modules .
 */
    'modules'  => ['form_id'],


/*
 * Views for the page  .
 */
    'views'    => ['default' => 'Default', 'left' => 'Left menu', 'right' => 'Right menu'],

// Modale variables for page module.
    'form_id'     => [
        'model'        => 'App\Models\FormId',
        'table'        => 'form_ids',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        //'slugs'        => ['slug' => 'name'],
        'fillable'     => ['user_id','form_id','open_id','status', 'created_at','updated_at'],
        'translate'    => [],
        'upload_folder' => '/form_id',
        'encrypt'      => ['id'],
        'revision'     => ['title'],
        'perPage'      => '20',
        'search'        => [
            'title' => 'like',
            'url'  => 'like',
        ],
    ],

];
