<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ResourceController as BaseController;
use Auth;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Repositories\Eloquent\TradeRecordRepositoryInterface;

/**
 * Resource controller class for user.
 */
class TradeRecordResourceController extends BaseController
{

    /**
     * Initialize user resource controller.
     *
     * @param type TradeRecordRepositoryInterface $tradeRecordRepository
     */

    public function __construct(
        TradeRecordRepositoryInterface $tradeRecordRepository
    )
    {
        parent::__construct();
        $this->repository = $tradeRecordRepository;
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
            $trade_records = $this->repository->join('users','users.id','=','trade_records.user_id');
            if(!empty($search_name))
            {
                $trade_records = $trade_records->where(function ($query) use ($search_name){
                    return $query->where('users.phone','like','%'.$search_name.'%')->orWhere('users.nickname','like','%'.$search_name.'%');
                });
            }
            if(!empty($user_id))
            {
                $trade_records = $trade_records->where(function ($query) use ($user_id){
                    return $query->where('users.id',$user_id);
                });
            }

            $trade_records = $trade_records
                ->orderBy('id','desc')
                ->paginate($limit,['trade_records.*','users.nickname','users.avatar_url','users.phone']);
            foreach ($trade_records as $key => $trade_record)
            {
                $trade_record->price_desc = $trade_record->type == 1 ? '+'.$trade_record->price : '-'.$trade_record->price;
            }
            return $this->response
                ->success()
                ->count($trade_records->total())
                ->data($trade_records->toArray()['data'])
                ->output();
        }
        return $this->response->title(trans('app.name'))
            ->view('trade_record.index')
            ->data(compact('search_name','user_id'))
            ->output();
    }


}