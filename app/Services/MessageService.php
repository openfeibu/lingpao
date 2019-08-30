<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use App\Repositories\Eloquent\UserRepositoryInterface;
use EasyWeChat\Factory;

class MessageService
{

	protected $request;

	protected $userRepository;

	function __construct(Request $request,
                         UserRepositoryInterface $userRepository
						 )
	{
		$this->request = $request;
		$this->userRepository = $userRepository;
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
        $config = [
            'app_id' => config('wechat.mini_program.default.app_id'),
            'token' => config('wechat.mini_program.default.token'),
            'aes_key'=> config('wechat.mini_program.default.aes_key'),
            'mch_id' => config('wechat.payment.default.mch_id'),
            'key' => config('wechat.payment.default.key'),
        ];
        $app = Factory::payment($config);
        switch ($data['type'])
        {
            case 'accept_order':
                $template_id = '';
                $page = '';
                break;
            case 'deliverer_cancel_order':
                $template_id = '';
                $page = '';
                break;
            case 'user_agree_cancel_order':
                $template_id = '';
                $page = '';
                break;
        }
        $app->template_message->send([
            'touser' => $data['open_id'],
            'template_id' => $template_id,
            'page' => 'index',
            'form_id' => $data['form_id'],
            'data' => [
                'keyword1' => 'VALUE',
                'keyword2' => 'VALUE2',
                // ...
            ],
        ]);
    }
}