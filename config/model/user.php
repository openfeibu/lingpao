<?php

return [
    /*
     * Package.
     */
    'package'  => 'user',

    /*
     * Modules.
     */
    'modules'  => ['admin'],
    /*
     * Additional user types other than user.
     */
    'types'    => ['client'],

    'policies' => [
        // Bind User policy
        \App\Models\AdminUser::class                 => \App\Policies\AdminUserPolicy::class,
    ],

    'admin'     => [
        'model' => [
            'model'         => \App\Models\AdminUser::class,
            'table'         => 'admin_users',
            //'presenter'     => \Litepie\User\Repositories\Presenter\UserPresenter::class,
            'hidden'        => [],
            'visible'       => [],
            'guarded'       => ['*'],
            'slugs'         => [],
            'dates'         => ['created_at', 'updated_at', 'deleted_at', 'dob'],
            'appends'       => [],
            'fillable'      => ['user_id', 'name', 'email', 'parent_id', 'password', 'api_token', 'remember_token', 'gender', 'dob', 'designation', 'mobile', 'phone', 'address', 'street', 'city', 'district', 'state', 'country', 'photo', 'web', 'permissions'],
            'translate'     => [],

            'upload_folder' => 'user/user',
            'uploads'       => [
                'photo' => [
                    'count' => 1,
                    'type'  => 'image',
                ],
            ],
            'casts'         => [
                'permissions' => 'array',
                'photo'       => 'array',
                'dob'         => 'date',
            ],
            'revision'      => [],
            'perPage'       => '20',
            'search'        => [
                'name'        => 'like',
                'email'       => 'like',
                'gender'         => 'like',
                'dob'         => 'like',
                'designation' => 'like',
                'mobile'      => 'like',
                'street'      => 'like',
                'status'      => 'like',
                'created_at'  => 'like',
                'updated_at'  => 'like',
            ],
        ],

    ],
    'user'     => [
        'model'         => \App\Models\User::class,
        'table'         => 'users',
        'hidden'        => [],
        'visible'       => [],
        'user_visible'  => ['id','open_id','nickname','phone','avatar_url','city','gender','token','session_key','is_pay_password','balance','role','gender'],
        'other_visible' => ['id','nickname','avatar_url','gender','role'],
        'guarded'       => ['*'],
        //'slugs'         => [],
        'dates'         => ['created_at', 'updated_at'],
        'fillable'      => ['name','email','nickname','open_id','session_key','phone','avatar_url','city','gender','password','remember_token','pay_password','balance','role','client_id','created_at','updated_at','verified','verification_token'],
        'translate'     => [],
        'upload_folder' => 'user/user',
        'uploads'       => [
            'photo' => [
                'count' => 1,
                'type'  => 'image',
            ],
        ],
        'casts'         => [
        ],
        'revision'      => [],
        'perPage'       => '20',
        'search'        => [
            'name'        => 'like',
            'email'       => 'like',
        ],


    ],
    'user_address'  => [
        'model'         => \App\Models\UserAddress::class,
        'table'         => 'user_addresses',
        'hidden'        => [],
        'visible'       => [],
        'guarded'       => ['*'],
        //'slugs'         => [],
        'dates'         => ['created_at', 'updated_at'],
        'appends'       => [],
        'fillable'      => ['user_id','consignee','mobile','address','is_default','created_at','updated_at'],
        'translate'     => [],
        'upload_folder' => 'user/user',
        'casts'         => [
        ],
        'revision'      => [],
        'perPage'       => '20',
        'search'        => [

        ],
    ],
    'withdraw'  => [
        'model'         => \App\Models\Withdraw::class,
        'table'         => 'withdraws',
        'hidden'        => [],
        'visible'       => [],
        'guarded'       => ['*'],
        //'slugs'         => [],
        'dates'         => ['created_at', 'updated_at'],
        'appends'       => [],
        'fillable'      => ['user_id','partner_trade_no','price','status','content','created_at','updated_at'],
        'translate'     => [],
        'upload_folder' => 'user/user',
        'casts'         => [
        ],
        'revision'      => [],
        'perPage'       => '20',
        'search'        => [

        ],
    ],
    'deliverer_identification'  => [
        'model'         => \App\Models\DelivererIdentification::class,
        'table'         => 'deliverer_identifications',
        'hidden'        => [],
        'visible'       => [],
        'guarded'       => ['*'],
        //'slugs'         => [],
        'dates'         => ['created_at', 'updated_at'],
        'appends'       => [],
        'fillable'      => ['user_id','name','student_id_card_image','content','status','created_at','updated_at'],
        'translate'     => [],
        'upload_folder' => 'user/user',
        'casts'         => [
        ],
        'revision'      => [],
        'perPage'       => '20',
        'search'        => [

        ],
    ],
];
