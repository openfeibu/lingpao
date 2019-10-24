<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ResourceController as BaseController;
use App\Repositories\Eloquent\TaskOrderRepositoryInterface;
use App\Repositories\Eloquent\SendOrderRepositoryInterface;
use Auth;
use Illuminate\Http\Request;
use App\Models\User;

/**
 * Resource controller class for user.
 */
class SendOrderResourceController extends BaseController
{

    /**
     * Initialize user resource controller.
     *
     * @param type SendOrderRepositoryInterface $sendOrderRepository
     * @param type TaskOrderRepositoryInterface $taskOrderRepository
     */

    public function __construct(
        SendOrderRepositoryInterface $sendOrderRepository,
        TaskOrderRepositoryInterface $taskOrderRepository
    )
    {
        parent::__construct();
        $this->repository = $sendOrderRepository;
        $this->taskOrderRepository = $taskOrderRepository;
        $this->repository
            ->pushCriteria(\App\Repositories\Criteria\RequestCriteria::class);
    }

    public function index(Request $request)
    {
        if ($this->response->typeIs('json')) {
            $type = 'send_order';
            $orders_data = $this->taskOrderRepository->getAdminTaskOrders($type);

            return $this->response
                ->success()
                ->count($orders_data['count'])
                ->data($orders_data['data'])
                ->output();
        }
        return $this->response->title(trans('app.name'))
            ->view('send_order.index')
            ->output();
    }

    public function show(Request $request,$id)
    {
        $send_order = $this->repository->getAdminOrder($id);

        return $this->response->title(trans('app.name'))
            ->data(compact('send_order'))
            ->view('send_order.show')
            ->output();
    }
}