<?php

return [

    /*
     * Modules .
     */
    'modules'  => ['task_order'],


    /*
     * Views for the page  .
     */
    'views'    => ['default' => 'Default', 'left' => 'Left menu', 'right' => 'Right menu'],

// Modale variables for page module.
    'task_order'     => [
        'model'        => 'App\Models\TaskOrder',
        'table'        => 'task_orders',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        'slugs'        => [],
        'fillable'     => ['name','objective_model','objective_id','type','order_status','created_at','updated_at'],
        'translate'    => [],
        'upload_folder' => '/take_order',
        'encrypt'      => ['id'],
        'revision'     => ['name', 'title'],
        'perPage'      => '20',
        'search'        => [
        ],
    ],
];
