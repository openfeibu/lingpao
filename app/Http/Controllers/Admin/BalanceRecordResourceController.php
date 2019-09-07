<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ResourceController as BaseController;
use Auth;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Repositories\Eloquent\BalanceRecordRepositoryInterface;

/**
 * Resource controller class for user.
 */
class BalanceRecordResourceController extends BaseController
{

    /**
     * Initialize user resource controller.
     *
     * @param type BalanceRecordRepositoryInterface $balance_record
     */

    public function __construct(
        BalanceRecordRepositoryInterface $balanceRecordRepository
    )
    {
        parent::__construct();
        $this->repository = $balanceRecordRepository;
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
            $balance_records = $this->repository->join('users','users.id','=','balance_records.user_id');
            if(!empty($search_name))
            {
                $balance_records = $balance_records->where(function ($query) use ($search_name){
                    return $query->where('users.phone','like','%'.$search_name.'%')->orWhere('users.nickname','like','%'.$search_name.'%');
                });
            }
            if(!empty($user_id))
            {
                $balance_records = $balance_records->where(function ($query) use ($user_id){
                    return $query->where('users.id',$user_id);
                });
            }

            $balance_records = $balance_records
                ->orderBy('id','desc')
                ->paginate($limit,['balance_records.*','users.nickname','users.avatar_url','users.phone']);
            foreach ($balance_records as $key => $balance_record)
            {
                $balance_record->price_desc = $balance_record->type == 1 ? '+'.$balance_record->price : '-'.$balance_record->price;
            }
            return $this->response
                ->success()
                ->count($balance_records->total())
                ->data($balance_records->toArray()['data'])
                ->output();
        }
        return $this->response->title(trans('app.name'))
            ->view('balance_record.index')
            ->data(compact('search_name','user_id'))
            ->output();
    }


}