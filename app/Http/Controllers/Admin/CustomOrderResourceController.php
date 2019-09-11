<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ResourceController as BaseController;
use App\Repositories\Eloquent\TaskOrderRepositoryInterface;
use App\Repositories\Eloquent\CustomOrderRepositoryInterface;
use Auth;
use Illuminate\Http\Request;
use App\Models\User;

/**
 * Resource controller class for user.
 */
class CustomOrderResourceController extends BaseController
{

    /**
     * Initialize user resource controller.
     *
     * @param type CustomOrderRepositoryInterface $customOrderRepository
     * @param type TaskOrderRepositoryInterface $taskOrderRepository
     *
     */

    public function __construct(
        CustomOrderRepositoryInterface $customOrderRepository,
        TaskOrderRepositoryInterface $taskOrderRepository
    )
    {
        parent::__construct();
        $this->repository = $customOrderRepository;
        $this->taskOrderRepository = $taskOrderRepository;
        $this->repository
            ->pushCriteria(\App\Repositories\Criteria\RequestCriteria::class);
    }

    public function index(Request $request)
    {
        if ($this->response->typeIs('json')) {
            $type = 'custom_order';
            $orders_data = $this->taskOrderRepository->getAdminTaskOrders($type);

            return $this->response
                ->success()
                ->count($orders_data['count'])
                ->data($orders_data['data'])
                ->output();
        }
        return $this->response->title(trans('app.name'))
            ->view('custom_order.index')
            ->output();
    }

    public function show(Request $request,$id)
    {
        $custom_order = $this->repository->getAdminOrder($id);

        return $this->response->title(trans('app.name'))
            ->data(compact('custom_order'))
            ->view('custom_order.show')
            ->output();
    }
}