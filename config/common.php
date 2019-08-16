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
    'wechat_notify_url' =>  config("app.api_url").'/payment/wechat-notify/',
];
