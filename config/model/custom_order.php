<?php

return [

    /*
     * Modules .
     */
    'modules'  => ['custom_order'],


    /*
     * Views for the page  .
     */
    'views'    => ['default' => 'Default', 'left' => 'Left menu', 'right' => 'Right menu'],

// Modale variables for page module.
    'custom_order'     => [
        'model'        => 'App\Models\CustomOrder',
        'table'        => 'custom_orders',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        'slugs'        => [],
        'fillable'     => ['id','order_sn','custom_order_type_id','user_id','deliverer_id','tip','coupon_id','coupon_name','coupon_price','original_price','total_price','best_time','order_status','order_cancel_status','payment','deliverer_price','postscript','created_at','updated_at'],
        'translate'    => [],
        'upload_folder' => '/custom_order',
        'encrypt'      => ['id'],
        'revision'     => [],
        'perPage'      => '20',
        'search'        => [
        ],
    ],
    'custom_order_category'     => [
        'model'        => 'App\Models\CustomOrderCategory',
        'table'        => 'custom_order_categories',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        'slugs'        => [],
        'fillable'     => ['id','name','slug','order'],
        'translate'    => [],
        'upload_folder' => '/custom_order',
        'encrypt'      => ['id'],
        'revision'     => [],
        'perPage'      => '20',
        'search'        => [
        ],
    ],
];
