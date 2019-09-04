<?php

return [
    'take_order' => [
        'be_accepted' => '您好，您的代拿任务已被接。',
        'be_finished' => '您好，接单人已完成您的代拿任务，请给任务结算和评价吧。',
        'order_status' => [
            'unpaid' => '待支付',
            'new' => '可接单',
            'cancel'=> '取消',
            'accepted' => '已接单',
            'finish' => '已完成',
            'completed' => '已结算',
            'remarked' => '已评价'
        ],
        'order_cancel_status' => [
            'user_apply_cancel' => '用户申请取消',
            'deliverer_apply_cancel' => '骑手申请取消',
            'user_agree_cancel' => '用户同意取消',
            'refunding' => '退款中',
            'refunded' => '已退款',
        ],
        'user_status_desc' => [
            'unpaid' => '待支付',
            'new' => '未接单',
            'cancel'=> '取消',
            'accepted' => '已接单',
            'finish' => '待结算',
            'completed' => '待评价',
            'remarked' => '已评价',
            'user_apply_cancel' => '取消中',
            'deliverer_apply_cancel' => '取消中',
            'user_agree_cancel' => '取消中',
            'refunding' => '退款中',
            'refunded' => '已退款',
        ],
        'service_price_pay_status' => [
            'unpaid' => '待支付',
            'paid' => '已支付'
        ],
    ],

];
