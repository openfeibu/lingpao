<?php

return [

/*
 * Modules .
 */
    'modules'  => ['remark'],


/*
 * Views for the page  .
 */
    'views'    => ['default' => 'Default', 'left' => 'Left menu', 'right' => 'Right menu'],

// Modale variables for page module.
    'remark'     => [
        'model'        => 'App\Models\Remark',
        'table'        => 'remarks',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        'fillable'     => ['user_id','deliverer_id','service_grade','speed_grade','comment','task_order_id','created_at','updated_at'],
        'translate'    => [],
        'upload_folder' => '/page/link',
        'encrypt'      => ['id'],
        'revision'     => ['name'],
        'perPage'      => '20',
        'search'        => [
        ],
    ],
];
