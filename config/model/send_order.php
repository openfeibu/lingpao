<?php

return [

    /*
     * Modules .
     */
    'modules'  => ['send_order','send_order_express_company','send_order_item_type'],


    /*
     * Views for the page  .
     */
    'views'    => ['default' => 'Default', 'left' => 'Left menu', 'right' => 'Right menu'],

    'send_order'     => [
        'model'        => 'App\Models\SendOrder',
        'table'        => 'send_orders',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        'slugs'        => [],
        'fillable'     => ['order_sn','user_id','deliverer_id','urgent','urgent_price','tip','coupon_id','coupon_name','coupon_price','original_price','total_price','order_status','order_cancel_status','payment','express_count','express_price','deliverer_price','postscript','fee','created_at','updated_at'],
        'translate'    => [],
        'upload_folder' => '/take_order',
        'encrypt'      => ['id'],
        'revision'     => [],
        'perPage'      => '20',
        'search'        => [
            'order_sn'  => 'like',
        ],
    ],
    'send_order_express_company' => [
        'model'        => 'App\Models\SendOrderExpressCompany',
        'table'        => 'send_order_express_companies',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        'slugs'        => [],
        'fillable'     => ['id','name','order','created_at','updated_at'],
        'translate'    => [],
        'upload_folder' => '/send_order',
        'encrypt'      => ['id'],
        'revision'     => [],
        'perPage'      => '20',
        'search'        => [
            'order_sn'  => 'like',
        ],
    ],
    'send_order_item_type' => [
        'model'        => 'App\Models\SendOrderItemType',
        'table'        => 'send_order_item_types',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        'slugs'        => [],
        'fillable'     => ['id','name','order','created_at','updated_at'],
        'translate'    => [],
        'upload_folder' => '/send_order',
        'encrypt'      => ['id'],
        'revision'     => [],
        'perPage'      => '20',
        'search'        => [
            'order_sn'  => 'like',
        ],
    ],
];
