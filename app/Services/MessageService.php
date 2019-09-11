<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use EasyWeChat\Factory;
use App\Models\FormId;

class MessageService
{

	protected $request;

	protected $userRepository;

	function __construct(Request $request)
	{
		$this->request = $request;
	}
    public function sendMessage($data,$client='weapp')
    {
        switch ($client)
        {
            case 'weapp':
                return $this->sendWeAppMessageHandle($data);
                break;
        }
    }
    public function sendWeAppMessageHandle($data)
    {
        $user = User::getUserById($data['user_id'],['id','open_id']);
        $form_id = FormId::getFormId($data['user_id']);
        if(!$form_id)
        {
            return "error";
        }
        $result = $this->sendWeAppMessage($data,$form_id,$user->open_id);

        if($result['errcode'] == 45009)
        {
            return "error";
        }
        if($result['errcode'] == 41028)
        {
            FormId::where('form_id',$form_id)->update(['status' => 'invalid']);
        }
        if($result['errcode'] == 41029)
        {
            FormId::where('form_id',$form_id)->update(['status' => 'used']);
        }
        if($result['errcode'] != 0)
        {
            return $this->sendWeAppMessageHandle($data);
        }
        FormId::where('form_id',$form_id)->update(['status' => 'used','use_type' => $data['type']]);
        return "success";

    }
    public function sendWeAppMessage($data,$form_id,$open_id)
    {
        $config = [
            'app_id' => config('wechat.mini_program.default.app_id'),
            'secret' =>  config('wechat.mini_program.default.secret'),
//            'token' => config('wechat.mini_program.default.token'),
//            'aes_key'=> config('wechat.mini_program.default.aes_key'),
        ];
        $app = Factory::miniProgram($config);
        switch ($data['type'])
        {
            case 'accept_order':
                $template_id = config('wechat.mini_program.default.template_id.accept_order');
                $data = [
                    'keyword1' => $data['nickname'],
                    'keyword2' => date('Y-m-d H:i:s'),
                    'keyword3' => sprintf(trans('task_order.be_accepted'),trans('task_order.'.$data['task_type'].'.name')),
                ];
                $page = '';
                break;
            case 'finish_order':
                $template_id = config('wechat.mini_program.default.template_id.status_change');
                $data = [
                    'keyword1' => trans('task_order.'.$data['task_type'].'.name'),
                    'keyword2' => $data['order_sn'],
                    'keyword3' => trans('task_order.order_status.finish'),
                    'keyword4' => sprintf(trans('task_order.be_finished'),trans('task_order.'.$data['task_type'].'.name')),
                ];
                $page = '';
                break;
            case 'complete_order':
                $template_id = config('wechat.mini_program.default.template_id.status_change');
                $data = [
                    'keyword1' => trans('task_order.'.$data['task_type'].'.name'),
                    'keyword2' => $data['order_sn'],
                    'keyword3' => trans('task_order.order_status.completed'),
                    'keyword4' => sprintf(trans('task_order.be_completed'),trans('task_order.'.$data['task_type'].'.name')),
                ];
                $page = '';
                break;
            case 'deliverer_cancel_order':
                $template_id = config('wechat.mini_program.default.template_id.status_change');
                $data = [
                    'keyword1' => trans('task_order.'.$data['task_type'].'.name'),
                    'keyword2' => $data['order_sn'],
                    'keyword3' => trans('task_order.order_cancel_status.deliverer_apply_cancel'),
                    'keyword4' => sprintf(trans('task_order.be_canceled'),trans('task_order.'.$data['task_type'].'.name')),
                ];
                $page = '';
                break;
            case 'user_agree_cancel_order':
                $template_id = config('wechat.mini_program.default.template_id.status_change');
                $data = [
                    'keyword1' => trans('task_order.'.$data['task_type'].'.name'),
                    'keyword2' => $data['order_sn'],
                    'keyword3' => trans('task_order.order_cancel_status.user_agree_cancel'),
                    'keyword4' => sprintf(trans('task_order.be_agree_cancel'),trans('task_order.'.$data['task_type'].'.name')),
                ];
                $page = '';
                break;
            case 'user_disagree_cancel_order':
                $template_id = config('wechat.mini_program.default.template_id.status_change');
                $data = [
                    'keyword1' => trans('task_order.'.$data['task_type'].'.name'),
                    'keyword2' => $data['order_sn'],
                    'keyword3' => trans('task_order.order_cancel_status.user_disagree_cancel'),
                    'keyword4' => sprintf(trans('task_order.be_disagree_cancel'),trans('task_order.'.$data['task_type'].'.name')),
                ];
                $page = '';
                break;
            case 'extra_price_pay':
                $template_id = config('wechat.mini_program.default.template_id.wait_pay');
                $data = [
                    'keyword1' => '骑手增加代拿服务费',
                    'keyword2' => $data['total_price'],
                ];
                $page = '';
                break;
            case 'extra_price_paid':
                $template_id = config('wechat.mini_program.default.template_id.status_change');
                $data = [
                    'keyword1' => trans('task_order.'.$data['task_type'].'.name'),
                    'keyword2' => $data['order_sn'],
                    'keyword3' => '用户已支付代拿服务费',
                    'keyword4' => '请及时完成任务',
                ];
                $page = '';
                break;
            case 'check':
                $template_id = config('wechat.mini_program.default.template_id.check');
                $data = [
                    'keyword1' => $data['content'],
                    'keyword2' => date('Y-m-d'),
                ];
                $page = '';
                break;
            case 'chat':
                $template_id = config('wechat.mini_program.default.template_id.chat');
                $data = [
                    'keyword1' => $data['from_nickname'],
                    'keyword2' => $data['content'],
                ];
                $page = '';
                break;
        }
        $page = $page ? $page : '/pages/index/index';
        $result = $app->template_message->send([
            'touser' => $open_id,
            'template_id' => $template_id,
            'page' => $page,
            'form_id' => $form_id,
            'data' => $data,
        ]);

        return $result;
    }
}