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
        'roles' => [
            'common' => '普通',
            'deliverer' => '骑手',
            'expert_deliverer' => '骑士',
        ],
    ],
    'deliverer_identification' => [
        'status' => [
            'checking' => '审核中',
            'passed' => '审核通过',
            'invalid' => '审核不通过',
        ],
    ],
];
