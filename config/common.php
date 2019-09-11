<?php

return [
    'img_type' => [
        'jpeg','jpg','gif','gpeg','png'
    ],
    'excel_type' => [
        'xls','xlsx','bin'
    ],
    'img_size' => 1024 * 1024 * 10,
    'file_size' => 1024 * 1024 * 10,
    'default_avatar' => '/system/avatar.jpeg',
    'auth_file' => '/system/auth_file.jpeg',
    'wechat_notify_url' => config("app.api_url").'/wechat/notify',
    'user' => [
        'roles' => ['common','deliverer','expert_deliverer'],
    ],
    'deliverer_identification' => [
        'status' => ['checking','passed','invalid'],
    ],
    'task_order' => [
        'order_status' => [ 'new' , 'cancel','accepted','finish','completed','remarked']
    ],
];
