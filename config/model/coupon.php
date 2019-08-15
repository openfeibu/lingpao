<?php

return [

/*
 * Modules .
 */
    'modules'  => ['coupon','user_coupon'],


/*
 * Views for the page  .
 */
    'views'    => ['default' => 'Default', 'left' => 'Left menu', 'right' => 'Right menu'],

// Modale variables for page module.
    'coupon'     => [
        'model'        => 'App\Models\Coupon',
        'table'        => 'coupons',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        'fillable'     => ['price', 'min_price', 'order'],
        'translate'    => ['price', 'min_price', 'order'],
        'upload_folder' => '/page/link',
        'encrypt'      => ['id'],
        'revision'     => ['name'],
        'perPage'      => '20',
        'search'        => [
            'title'  => 'like',
        ],
    ],
    'user_coupon' => [
        'model'        => 'App\Models\UserCoupon',
        'table'        => 'user_coupons',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        'fillable'     => ['user_id', 'price', 'min_price','receive','overdue','status'],
        'translate'    => ['user_id', 'price', 'min_price','receive','overdue','status'],
        'upload_folder' => '/page/link',
        'encrypt'      => ['id'],
        'revision'     => ['name'],
        'perPage'      => '20',
        'search'        => [
            'title'  => 'like',
        ],
    ],
];
