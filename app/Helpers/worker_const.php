<?php
// 注册协议
define('GW_REGISTER_PROTOCOL','0.0.0.0:12360');

// 注册地址
define('GW_REGISTER_ADDRESS','127.0.0.1:12360');

// 网关地址
define('GW_GATEWAY_ADDRESS','0.0.0.0:23460');

// 网关起始端口
define('GW_GATEWAY_START_PORT','2300');

define('GW_TRANSPORT','ssl');

// 心跳检测间隔，单位：秒，0 表示不发送心跳检测
define('GW_GATEWAY_PING_INTERVAL',30);

define('GW_GATEWAY_PING_NOT_RESPONSE_LIMIT',1);

// 本机ip，分布式部署时请设置成内网ip（非127.0.0.1）
define('GW_LOCAL_HOST_IP','127.0.0.1');

// 网关名称
define('GW_GATEWAY_NAME','Gateway001');

// worker进程名称
define('GW_WORKER_NAME','BusinessWorker001');

// Gateway进程数量，建议与CPU核数相同
define('GW_GATEWAY_COUNT',2);

// BusinessWorker进程数量，建议设置为CPU核数的1倍-3倍
define('GW_BUSINESS_WORKER_COUNT',2);

// Business业务处理类，可以带命名空间
define('GW_BUSINESS_EVENT_HANDLER',\App\Events\GatewayWorker\Events::class);

define('GW_BUSINESS_EVENT_HANDLER_WIN','App\Events\GatewayWorker\WinEvents');