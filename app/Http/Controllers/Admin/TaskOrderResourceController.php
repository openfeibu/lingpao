<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ResourceController as BaseController;
use Auth;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Repositories\Eloquent\TaskOrderRepositoryInterface;

/**
 * Resource controller class for user.
 */
class TaskOrderResourceController extends BaseController
{

    /**
     * Initialize user resource controller.
     *
     * @param type TaskOrderRepositoryInterface $taskOrderRepository
     */

    public function __construct(
        TaskOrderRepositoryInterface $taskOrderRepository
    )
    {
        parent::__construct();
        $this->repository = $taskOrderRepository;
        $this->repository
            ->pushCriteria(\App\Repositories\Criteria\RequestCriteria::class);
    }
    public function index(Request $request)
    {
        $limit = $request->input('limit',config('app.limit'));
        $search = $request->input('search',[]);
        $user_id = $request->input('user_id','');
        $search_name = isset($search['search_name']) ? $search['search_name'] : '';
        if ($this->response->typeIs('json')) {
            $type = Request::get('type','all');
            $limit = Request::get('limit',config('app.limit'));
            $orders = $this->model->select(DB::raw('*'))
                ->whereNotIn('order_status', ['unpaid']);
            if($type != 'all')
            {
                $orders = $orders->where('type',$type);
            }

            $orders = $orders->orderBy('id','desc')->paginate($limit);

            $orders_data = [];
            foreach ($orders as $key => $order)
            {
                switch ($order->type)
                {
                    case 'take_order':
                        $order_detail = app(TakeOrderRepository::class)->getOrder($order->objective_id);
                        break;
                    case 'custom_order':
                        $order_detail = app(CustomOrderRepository::class)->getOrder($order->objective_id);
                        break;
                }

                $order_detail->task_order_id = $order->id;
                $order_detail->type = $order->type;
                $orders_data[] = $order_detail;
            }
        }
        return $this->response->title(trans('app.name'))
            ->view('balance_record.index')
            ->data(compact('search_name','user_id'))
            ->output();
    }


}