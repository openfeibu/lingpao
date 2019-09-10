<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ResourceController as BaseController;
use App\Repositories\Eloquent\TaskOrderRepositoryInterface;
use App\Repositories\Eloquent\TakeOrderRepositoryInterface;
use Auth;
use Illuminate\Http\Request;
use App\Models\User;

/**
 * Resource controller class for user.
 */
class TakeOrderResourceController extends BaseController
{

    /**
     * Initialize user resource controller.
     *
     * @param type TakeOrderRepositoryInterface $takeOrderRepository
     */

    public function __construct(
        TakeOrderRepositoryInterface $takeOrderRepository,
        TaskOrderRepositoryInterface $taskOrderRepository
    )
    {
        parent::__construct();
        $this->repository = $takeOrderRepository;
        $this->taskOrderRepository = $taskOrderRepository;
        $this->repository
            ->pushCriteria(\App\Repositories\Criteria\RequestCriteria::class);
    }

    public function index(Request $request)
    {
        if ($this->response->typeIs('json')) {
            $type = 'take_order';
            $orders_data = $this->taskOrderRepository->getAdminTaskOrders($type);

            return $this->response
                ->success()
                ->count($orders_data['count'])
                ->data($orders_data['data'])
                ->output();
        }
        return $this->response->title(trans('app.name'))
            ->view('take_order.index')
            ->output();
    }

    public function show(Request $request,$id)
    {
        $take_order = $this->repository->getAdminOrder($id);
        return $this->response->title(trans('app.name'))
            ->data(compact('take_order'))
            ->view('take_order.show')
            ->output();
    }
}