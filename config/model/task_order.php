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
        'fillable'     => ['order_sn','name','user_id','deliverer_id','objective_model','objective_id','type','order_status','order_cancel_status','created_at','updated_at'],
        'translate'    => [],
        'upload_folder' => '/take_order',
        'encrypt'      => ['id'],
        'revision'     => ['name', 'title'],
        'perPage'      => '20',
        'search'        => [
        ],
    ],
    'task_order_status_change'     => [
        'model'        => 'App\Models\TaskOrderStatusChange',
        'table'        => 'task_order_status_changes',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        'slugs'        => [],
        'fillable'     => ['type','user_id','deliverer_id','objective_model','objective_id','order_status','order_cancel_status','created_at','updated_at'],
        'translate'    => [],
        'upload_folder' => '/',
        'encrypt'      => ['id'],
        'revision'     => ['name', 'title'],
        'perPage'      => '20',
        'search'        => [
        ],
    ],
];
