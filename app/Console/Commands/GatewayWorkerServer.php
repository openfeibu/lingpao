<?php

namespace App\Console\Commands;

use GatewayWorker\BusinessWorker;
use Illuminate\Console\Command;
use Workerman\Worker;
use GatewayWorker\Gateway;
use GatewayWorker\Register;

class GatewayWorkerServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gateway-worker:server {action} {--daemon}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start a GatewayWorker Server.';

    /**
     * constructor
     */
    public function __construct()
    {
        parent::__construct();
        include_once app_path().'/Helpers/worker_const.php';
    }

    /**
     * Execute the console command.
     *
     * [@return](https://learnku.com/users/31554) mixed
     */
    public function handle()
    {
        global $argv;

        if (!in_array($action = $this->argument('action'), ['start', 'stop', 'restart'])) {
            $this->error('Error Arguments');
            exit;
        }

        $argv[0] = 'gateway-worker:server';
        $argv[1] = $action;
        $argv[2] = $this->option('daemon') ? '-d' : '';

        $this->start();
    }

    private function start()
    {
        $this->startGateWay();
        $this->startBusinessWorker();
        $this->startRegister();
        Worker::runAll();
    }

    private function startBusinessWorker()
    {
        $worker                  = new BusinessWorker();
        $worker->name            = GW_WORKER_NAME;                        #设置BusinessWorker进程的名称
        $worker->count           = GW_BUSINESS_WORKER_COUNT;                                       #设置BusinessWorker进程的数量
        $worker->registerAddress = GW_REGISTER_ADDRESS;                        #注册服务地址
        $worker->eventHandler    = \App\Events\GatewayWorker\Events::class;            #设置使用哪个类来处理业务,业务类至少要实现onMessage静态方法，onConnect和onClose静态方法可以不用实现
    }

    private function startGateWay()
    {
        $gateway = new Gateway(sprintf('websocket://%s',GW_GATEWAY_ADDRESS));
        $gateway->name                 = GW_GATEWAY_NAME;                         #设置Gateway进程的名称，方便status命令中查看统计
        $gateway->count                = GW_GATEWAY_COUNT;                                 #进程的数量
        $gateway->lanIp                = GW_LOCAL_HOST_IP;                       #内网ip,多服务器分布式部署的时候需要填写真实的内网ip
        $gateway->startPort            = GW_GATEWAY_START_PORT;                              #监听本机端口的起始端口
        $gateway->pingInterval         = GW_GATEWAY_PING_INTERVAL;
        $gateway->pingNotResponseLimit = 0;                                 #服务端主动发送心跳
        $gateway->pingData             = '{"type":"heart"}';
        $gateway->registerAddress      = GW_REGISTER_ADDRESS;                  #注册服务地址
    }

    private function startRegister()
    {
        new Register(sprintf('text://%s',GW_REGISTER_PROTOCOL));
    }

}