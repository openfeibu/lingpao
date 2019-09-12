<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ResourceController as BaseController;
use App\Models\Remark;
use App\Services\MessageService;
use Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Repositories\Eloquent\RemarkRepositoryInterface;

/**
 * Resource controller class for user.
 */
class RemarkResourceController extends BaseController
{

    /**
     * Initialize user resource controller.
     *
     * @param type RemarkRepositoryInterface $remarkRepository
     */

    public function __construct(
        RemarkRepositoryInterface $remarkRepository
    )
    {
        parent::__construct();
        $this->repository = $remarkRepository;
        $this->repository
            ->pushCriteria(\App\Repositories\Criteria\RequestCriteria::class);
    }
    public function index(Request $request)
    {
        $limit = $request->input('limit',config('app.limit'));
        $deliverer_id = $request->input('deliverer_id','');

        if ($this->response->typeIs('json')) {
            $remarks = $this->repository->join('users','users.id','=','remarks.user_id')
            ->join('users as deliverers','deliverers.id','=','remarks.deliverer_id');

            if(!empty($deliverer_id))
            {
                $remarks = $remarks->where(function ($query) use ($deliverer_id){
                    return $query->where('deliverers.id',$deliverer_id);
                });
            }
            $remarks = $remarks
                ->orderBy('id','desc')
                ->paginate($limit,['remarks.*','users.nickname','users.avatar_url','users.phone','deliverers.nickname as deliverer_nickname','deliverers.avatar_url as deliverer_avatar_url','deliverers.phone as deliverer_phone']);

            return $this->response
                ->success()
                ->count($remarks->total())
                ->data($remarks->toArray()['data'])
                ->output();
        }
        return $this->response->title(trans('app.name'))
            ->view('remark.index')
            ->data(compact('deliverer_id'))
            ->output();
    }
}