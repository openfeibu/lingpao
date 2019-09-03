<?php

return [

/*
 * Modules .
 */
    'modules'  => ['trade_record'],


/*
 * Views for the page  .
 */
    'views'    => ['default' => 'Default', 'left' => 'Left menu', 'right' => 'Right menu'],

// Modale variables for page module.
    'trade_record'     => [
        'model'        => 'App\Models\TradeRecord',
        'table'        => 'trade_records',
        'primaryKey'   => 'id',
        'hidden'       => [],
        'visible'      => [],
        'guarded'      => ['*'],
        'fillable'     => ['out_trade_no','trade_no','user_id','trade_type','type','trade_status','description','pay_from','payment','price','fee','created_at','updated_at'],
        'upload_folder' => '/page/',
        'encrypt'      => ['id'],
        'revision'     => ['name'],
        'perPage'      => '20',
        'search'        => [
        ],
    ],
];
