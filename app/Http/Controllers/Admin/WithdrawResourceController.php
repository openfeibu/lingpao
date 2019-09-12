<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ResourceController as BaseController;
use App\Repositories\Eloquent\BalanceRecordRepositoryInterface;
use App\Repositories\Eloquent\TradeRecordRepositoryInterface;
use App\Repositories\Eloquent\UserRepositoryInterface;
use App\Repositories\Eloquent\WithdrawRepositoryInterface;
use Auth,Log;
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
        if ($this->response->typeIs('json')) {

            $withdraws = $this->repository
                ->join('users','users.id','=','withdraws.user_id')
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
            return $this->response->message("该状态不能操作提现")
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
            'partner_trade_no' => $withdraw->partner_trade_no, // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
            'openid' => $withdraw->open_id,
            'check_name' => 'NO_CHECK', // NO_CHECK：不校验真实姓名, FORCE_CHECK：强校验真实姓名
            're_user_name' => $withdraw->nickname, // 如果 check_name 设置为FORCE_CHECK，则必填用户真实姓名
            'amount' => $withdraw->price * 100, // 企业付款金额，单位为分
            'desc' => '提现', // 企业付款操作说明信息。必填
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
            return $this->response->message("支付成功")
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
    public function reject(Request $request)
    {
        $id = $request->id;
        $withdraw = $this->repository
            ->join('users','users.id','=','withdraws.user_id')
            ->orderBy('withdraws.id','desc')
            ->find($id,['users.nickname','users.avatar_url','users.phone','users.open_id','withdraws.*']);

        if(!in_array($withdraw->status,['checking','failed']))
        {
            return $this->response->message("该状态不能操作驳回")
                ->code(400)
                ->status('error')
                ->url(guard_url('withdraw'))
                ->redirect();
        }
        $trade = $this->tradeRecordRepository->where('out_trade_no',$withdraw->partner_trade_no)->first(['id']);
        $this->tradeRecordRepository->update(['trade_status'=>'cashfail','type' => 1],$trade->id);
        $balance = User::where('id',$withdraw->user_id)->value('balance');
        $new_balance = $balance + $withdraw->price ;
        //只能是字母或者数字，不能包含有其他字符
        $out_trade_no = generate_order_sn();
        $balanceData = array(
            'user_id' => $withdraw->user_id,
            'balance' => $new_balance,
            'price'	=> $withdraw->price,
            'out_trade_no' => $out_trade_no,
            'fee' => 0,
            'type' => 1,
            'trade_type' => 'WITHDRAWALS_FAILED',
            'description' => '提现失败',
        );
        $this->balanceRecordRepository->create($balanceData);
        $this->userRepository->update(['balance' => $new_balance],$withdraw->user_id);
        $this->repository->update([
            'status' => 'reject',
            'content' => $request->return_content,
        ],$id);

        return $this->response->message("操作成功")
            ->code(0)
            ->status('success')
            ->url(guard_url('withdraw'))
            ->redirect();
    }
}