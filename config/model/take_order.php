<?php

return [

    /*
     * Modules .
     */
    'modules'  => ['take_order','take_order_express'],


    /*
     * Views for the page  .
     */
    'views'    => ['default' => 'Default', 'left' => 'Left menu', 'right' => 'Right menu'],

// Modale variables for page module.
    'take_order'     => [
        'model'        => 'App\Models\TakeOrder',
        'table'        => 'take_orders',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        'slugs'        => [],
        'fillable'     => ['order_sn','user_id','deliverer_id','urgent','urgent_price','tip','coupon_id','coupon_name','coupon_price','original_price','total_price','order_status','payment','express_count','express_price','deliverer_price','postscript','created_at','updated_at'],
        'translate'    => [],
        'upload_folder' => '/take_order',
        'encrypt'      => ['id'],
        'revision'     => ['name', 'title'],
        'perPage'      => '20',
        'search'        => [
            'coupon_name'  => 'like',
            'order_sn'  => 'like',
        ],
    ],
    'take_order_express'     => [
        'model'        => 'App\Models\TakeOrderExpress',
        'table'        => 'take_order_expresses',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        'slugs'        => [],
        'fillable'     => ['take_order_id','take_place','consignee','mobile','address','description','take_code','express_company','express_arrive_date','created_at','updated_at'],
        'translate'    => [],
        'upload_folder' => '/take_order',
        'encrypt'      => ['id'],
        'revision'     => ['name', 'title'],
        'perPage'      => '20',
        'search'        => [
            'coupon_name'  => 'like',
            'order_sn'  => 'like',
        ],
    ],
];
