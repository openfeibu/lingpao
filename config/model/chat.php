<?php

return [

/*
 * Modules .
 */
    'modules'  => ['chat','room'],


/*
 * Views for the page  .
 */
    'views'    => ['default' => 'Default', 'left' => 'Left menu', 'right' => 'Right menu'],

// Modale variables for page module.
    'room'     => [
        'model'        => 'App\Models\Room',
        'table'        => 'rooms',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        'fillable'     => ['to_user_id', 'from_user_id', 'created_at','updated_at'],
        'translate'    => ['price', 'min_price', 'order'],
        'upload_folder' => '/coupon',
        'encrypt'      => ['id'],
        'revision'     => ['name'],
        'perPage'      => '20',
        'search'        => [
            'title'  => 'like',
        ],
    ],
    'chat' => [
        'model'        => 'App\Models\Chats',
        'table'        => 'chats',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        'fillable'     => ['room_id','to_user_id', 'from_user_id','type','content', 'unread','created_at','updated_at'],
        'translate'    => [],
        'upload_folder' => '/chat',
        'encrypt'      => ['id'],
        'revision'     => ['name'],
        'perPage'      => '20',
        'search'        => [
            'title'  => 'like',
        ],
    ],

];
