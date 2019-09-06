<?php

namespace App\Http\Controllers\Api;

use App\Events\GatewayWorker\Events;
use App\Exceptions\RequestSuccessException;
use App\Http\Controllers\Api\BaseController;
use App\Models\FormId;
use App\Models\User;
use App\Repositories\Eloquent\CustomOrderRepositoryInterface;
use App\Repositories\Eloquent\TakeOrderRepositoryInterface;
use App\Services\MessageService;
use App\Services\RefundService;
use Illuminate\Http\Request;
use Log,DB;

class ScheduleController extends BaseController
{
    public function __construct(TakeOrderRepositoryInterface $takeOrderRepository,
                                CustomOrderRepositoryInterface $customOrderRepository,
                                RefundService $refundService)
    {
        parent::__construct();
        $this->takeOrderRepository = $takeOrderRepository;
        $this->customOrderRepository = $customOrderRepository;
        $this->refundService = $refundService;
    }
    public function refund()
    {
        set_time_limit(0);
        $this->refundTakeOrder();
        $this->refundCustomOrder();
    }

    public function complete()
    {
        set_time_limit(0);
        $this->completeTakeOrder();
        $this->completeCustomOrder();
    }


}
