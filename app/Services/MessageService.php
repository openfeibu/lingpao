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
                return $this->sendWeAppMessage($data);
                break;
        }
    }
    public function sendWeAppMessage($data)
    {
        $user = User::getUserById($data['user_id'],['id','open_id']);
        $form_id = FormId::getFormId($data['user_id']);

        if(!$form_id)
        {
            return ;
        }
        $config = [
            'app_id' => config('wechat.mini_program.default.app_id'),
            'token' => config('wechat.mini_program.default.token'),
            'aes_key'=> config('wechat.mini_program.default.aes_key'),
            'mch_id' => config('wechat.payment.default.mch_id'),
            'key' => config('wechat.payment.default.key'),
        ];
        $app = Factory::miniProgram($config);
        switch ($data['type'])
        {
            case 'accept_order':
                $template_id = config('wechat.mini_program.default.template_id.accept_order');
                $page = '';
                break;
            case 'finish_order':
                $template_id = config('wechat.mini_program.default.template_id.status_change');
                $page = '';
                break;
            case 'deliverer_cancel_order':
                $template_id = config('wechat.mini_program.default.template_id.status_change');
                $page = '';
                break;
            case 'user_agree_cancel_order':
                $template_id = config('wechat.mini_program.default.template_id.user_agree_cancel_order');
                $page = '';
                break;
        }
        $result = $app->template_message->send([
            'touser' => $user->open_id,
            'template_id' => $template_id,
            'page' => $page,
            'form_id' => $form_id,
            'data' => $data['data'],
        ]);
        FormId::where('id',$form_id)->update(['status' => 'used','use_type' => $data['type']]);
        var_dump($result);exit;
    }
}