<?php

return [
    'label'       => [
        'order_sn' => '订单号',
        'total_price' => '总价',
        'urgent' => '加急',
        'coupon_name' => '优惠券',
        'coupon_price' => '优惠券价格',
        'order_status' => '状态',
        'payment' => '支付方式',
        'postscript' => '备注',
        'user' => '发单人',
        'deliverer' => '接单人',
    ],


    'take_order' => [
        'name' => '代拿',
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
    'custom_order' => [
        'name' => '帮帮忙',
    ],

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
    'be_accepted' => '您好，您的%s任务已被接。',
    'be_finished' => '您好，骑手已完成您的%s任务，请给任务结算和评价吧。',
    'be_completed' => '您好，发单人已结算您的%s任务接单。',
    'be_canceled' => '您好，骑手取消了您的%s任务。',
    'be_agree_cancel' => '您好，发单人同意取消了您的%s任务接单。',
    'refund_success' => "取消任务成功，任务费用已原路退回，请注意查收!",
];
