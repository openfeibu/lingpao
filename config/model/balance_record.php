<?php

return [

/*
 * Modules .
 */
    'modules'  => ['balance_record'],


/*
 * Views for the page  .
 */
    'views'    => ['default' => 'Default', 'left' => 'Left menu', 'right' => 'Right menu'],

// Modale variables for page module.
    'balance_record'     => [
        'model'        => 'App\Models\BalanceRecord',
        'table'        => 'balance_records',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        'fillable'     => ['user_id','out_trade_no','type','price','balance','fee','trade_type','description','created_at','updated_at'],
        'upload_folder' => '/page/',
        'encrypt'      => ['id'],
        'revision'     => ['name'],
        'perPage'      => '20',
        'search'        => [
        ],
    ],
];
