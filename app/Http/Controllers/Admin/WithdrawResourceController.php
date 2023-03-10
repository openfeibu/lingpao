<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ResourceController as BaseController;
use App\Models\Withdraw;
use App\Repositories\Eloquent\BalanceRecordRepositoryInterface;
use App\Repositories\Eloquent\TradeRecordRepositoryInterface;
use App\Repositories\Eloquent\UserRepositoryInterface;
use App\Repositories\Eloquent\WithdrawRepositoryInterface;
use Auth,Log,DB;
use Illuminate\Http\Request;
use App\Models\User;
use EasyWeChat\Factory;

/**
 * Resource controller class for user.
 */
class WithdrawResourceController extends BaseController
{

    /**
     * Initialize user resource controller.
     *
     * @param type WithdrawRepositoryInterface $withdrawRepository
     *
     */

    public function __construct(
        WithdrawRepositoryInterface $withdrawRepository,
        TradeRecordRepositoryInterface $tradeRecordRepository,
        BalanceRecordRepositoryInterface $balanceRecordRepository,
        UserRepositoryInterface $userRepository
    )
    {
        parent::__construct();
        $this->repository = $withdrawRepository;
        $this->tradeRecordRepository = $tradeRecordRepository;
        $this->balanceRecordRepository = $balanceRecordRepository;
        $this->userRepository = $userRepository;
        $this->repository
            ->pushCriteria(\App\Repositories\Criteria\RequestCriteria::class);
    }

    public function index(Request $request)
    {
        $limit = $request->input('limit',config('app.limit'));
        $search = $request->input('search',[]);
        $search_name = isset($search['search_name']) ? $search['search_name'] : '';
        if ($this->response->typeIs('json')) {

            $withdraws = Withdraw::join('users','users.id','=','withdraws.user_id')
                ->select(DB::raw('users.nickname,users.avatar_url,users.phone,withdraws.*,CASE status WHEN "checking" THEN 1 WHEN "failed" THEN 2 WHEN "reject" THEN 3 ELSE 4 END as status_num'));
            if(!empty($search_name))
            {
                $withdraws = $withdraws->where(function ($query) use ($search_name){
                    return $query->where('users.phone','like','%'.$search_name.'%')->orWhere('users.nickname','like','%'.$search_name.'%')->orWhere('users.id',$search_name);
                });
            }
            $withdraws = $withdraws->orderBy('status_num','asc')
                ->orderBy('withdraws.id','desc')
                ->paginate($limit,['users.nickname','users.avatar_url','users.phone','withdraws.*']);

            foreach ($withdraws as $key => $withdraw)
            {
                $withdraw->status_desc = $withdraw->status_desc;
            }

            return $this->response
                ->success()
                ->count($withdraws->total())
                ->data($withdraws->toArray()['data'])
                ->output();
        }
        return $this->response->title(trans('app.name'))
            ->view('withdraw.index')
            ->output();
    }

    public function show(Request $request,$id)
    {
        $withdraw = $this->repository
            ->join('users','users.id','=','withdraws.user_id')
            ->orderBy('withdraws.id','desc')
            ->select('users.nickname','users.avatar_url','users.phone','withdraws.*')
            ->find($id);

        return $this->response->title(trans('app.name'))
            ->data(compact('withdraw'))
            ->view('withdraw.show')
            ->output();
    }
    public function pass(Request $request)
    {
        $id = $request->id;

        $withdraw = $this->repository
            ->join('users','users.id','=','withdraws.user_id')
            ->orderBy('withdraws.id','desc')
            ->find($id,['users.nickname','users.avatar_url','users.phone','users.open_id','withdraws.*']);

        if(!in_array($withdraw->status,['checking','failed']))
        {
            return $this->response->message("???????????????????????????")
                ->code(400)
                ->status('error')
                ->url(guard_url('withdraw'))
                ->redirect();
        }

        $config = [
            'app_id' => config('wechat.mini_program.default.app_id'),
            'mch_id' => config('wechat.payment.default.mch_id'),
            'key' => config('wechat.payment.default.key'),
            'cert_path' => config('wechat.payment.default.cert_path'),
            'key_path' => config('wechat.payment.default.key_path') ,
        ];
        $app = Factory::payment($config);

        $result = $app->transfer->toBalance([
            'partner_trade_no' => $withdraw->partner_trade_no, // ????????????????????????????????????(???????????????????????????????????????????????????)
            'openid' => $withdraw->open_id,
            'check_name' => 'NO_CHECK', // NO_CHECK????????????????????????, FORCE_CHECK????????????????????????
            're_user_name' => $withdraw->nickname, // ?????? check_name ?????????FORCE_CHECK??????????????????????????????
            'amount' => $withdraw->price * 100, // ?????????????????????????????????
            'desc' => '??????', // ???????????????????????????????????????
        ]);
        Log::debug('withdraw_result:');
        Log::debug($result);
        $trade = $this->tradeRecordRepository->where('out_trade_no',$withdraw->partner_trade_no)->first(['id']);
        if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS')
        {
            $this->tradeRecordRepository->update(['trade_status'=>'cashed'],$trade->id);
            $this->repository->update([
                'status' => 'success',
                'payment_no' => $result['payment_no'],
                'payment_time' => $result['payment_time']
            ],$id);
            return $this->response->message("????????????")
                ->code(0)
                ->status('success')
                ->url(guard_url('withdraw'))
                ->redirect();
        }else{
            $this->repository->update([
                'status' => 'failed'
            ],$id);
            return $this->response->message($result['return_msg'])
                ->code(400)
                ->status('error')
                ->url(guard_url('withdraw'))
                ->redirect();
        }


    }
    public function paid(Request $request)
    {
        $id = $request->id;

        $withdraw = $this->repository
            ->join('users','users.id','=','withdraws.user_id')
            ->orderBy('withdraws.id','desc')
            ->find($id,['users.nickname','users.avatar_url','users.phone','users.open_id','withdraws.*']);

        if(!in_array($withdraw->status,['checking','failed']))
        {
            return $this->response->message("???????????????????????????")
                ->code(400)
                ->status('error')
                ->url(guard_url('withdraw'))
                ->redirect();
        }
        $this->repository->update([
            'status' => 'offline',
            'payment_time' => date('Y-m-d H:i:s')
        ],$id);
        return $this->response->message("????????????")
            ->code(0)
            ->status('success')
            ->url(guard_url('withdraw'))
            ->redirect();

    }
    public function reject(Request $request)
    {
        $id = $request->id;
        $withdraw = $this->repository
            ->join('users','users.id','=','withdraws.user_id')
            ->orderBy('withdraws.id','desc')
            ->find($id,['users.nickname','users.avatar_url','users.phone','users.open_id','withdraws.*']);

        if(!in_array($withdraw->status,['checking','failed']))
        {
            return $this->response->message("???????????????????????????")
                ->code(400)
                ->status('error')
                ->url(guard_url('withdraw'))
                ->redirect();
        }
        $trade = $this->tradeRecordRepository->where('out_trade_no',$withdraw->partner_trade_no)->first(['id']);
        $this->tradeRecordRepository->update(['trade_status'=>'cashfail','type' => 1],$trade->id);
        $balance = User::where('id',$withdraw->user_id)->value('balance');
        $new_balance = $balance + $withdraw->price ;
        //?????????????????????????????????????????????????????????
        $out_trade_no = generate_order_sn();
        $balanceData = array(
            'user_id' => $withdraw->user_id,
            'balance' => $new_balance,
            'price'	=> $withdraw->price,
            'out_trade_no' => $out_trade_no,
            'fee' => 0,
            'type' => 1,
            'trade_type' => 'WITHDRAWALS_FAILED',
            'description' => '????????????',
        );
        $this->balanceRecordRepository->create($balanceData);
        $this->userRepository->update(['balance' => $new_balance],$withdraw->user_id);
        $this->repository->update([
            'status' => 'reject',
            'content' => $request->return_content,
        ],$id);

        return $this->response->message("????????????")
            ->code(0)
            ->status('success')
            ->url(guard_url('withdraw'))
            ->redirect();
    }
}