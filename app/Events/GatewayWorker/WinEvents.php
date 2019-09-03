<?php

namespace App\Events\GatewayWorker;

define('LARAVEL_START', microtime(true));
require __DIR__.'/../../../vendor/autoload.php';
$app = require_once __DIR__.'/../../../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(
    $request = \Illuminate\Http\Request::capture()
);

use GatewayWorker\Lib\Gateway;
use Log;
use App\Models\Chat;
use App\Models\Room;
use App\Models\User;

class WinEvents extends  Events
{

}