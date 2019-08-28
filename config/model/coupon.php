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
        'upload_folder' => '/coupon',
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
        'upload_folder' => '/coupon',
        'encrypt'      => ['id'],
        'revision'     => ['name'],
        'perPage'      => '20',
        'search'        => [
            'title'  => 'like',
        ],
    ],
    'user_balance_coupon' => [
        'model'        => 'App\Models\UserBalanceCoupon',
        'table'        => 'user_balance_coupons',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        'fillable'     => ['user_id', 'price', 'balance','created_at','updated_at'],
        'translate'    => [],
        'upload_folder' => '/coupon',
        'encrypt'      => ['id'],
        'revision'     => ['name'],
        'perPage'      => '20',
        'search'        => [
            'title'  => 'like',
        ],
    ],
    'user_all_coupon' => [
        'model'        => 'App\Models\UserAllCoupon',
        'table'        => 'user_all_coupons',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        'fillable'     => ['user_id', 'type', 'objective_id','objective_model','created_at','updated_at'],
        'translate'    => [],
        'upload_folder' => '/coupon',
        'encrypt'      => ['id'],
        'revision'     => ['name'],
        'perPage'      => '20',
        'search'        => [
            'title'  => 'like',
        ],
    ],

];
