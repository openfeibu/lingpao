<?php

namespace App\Http\Controllers\Api;

use App\Events\GatewayWorker\Events;
use App\Exceptions\RequestSuccessException;
use App\Http\Controllers\Api\BaseController;
use App\Models\FormId;
use App\Models\User;
use App\Services\MessageService;
use Illuminate\Http\Request;
use App\Models\Banner;
use App\Models\Setting;
use Log;

class HomeController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getBanners(Request $request)
    {
        $banners = Banner::orderBy('order','asc')->orderBy('id','asc')->get()->toArray();
        foreach ($banners as $key => $val)
        {
            $banners[$key]['image'] = url('/image/original'.$val['image']);
        }
        return $this->response->success()->data($banners)->json();
    }
    public function setting(Request $request)
    {
        $category = $request->input('category','arguments');
        $arguments = Setting::where('category',$category)->orderBy('order','asc')->orderBy('id','asc')->get(['title','slug','value'])->toArray();
        $data = [];
        foreach ($arguments as $key => $argument)
        {
            $data[$argument['slug']] = [
                'value' => $argument['value'],
                'title' => $argument['title'],
            ];
        }
        return $this->response->success()->data($data)->json();
    }
    public function test()
    {
        /*
        $message = array();


//        $message = [
//            'token' => '67ea6c250717247ad5fad199c25f91271a8b41c7',
//            'type' => 'text',
//            'content' => '123',
//            'conversationId' => 0,
//            'friendId' => 4,
//        ];


//        $message = [
//            'token' => '67ea6c250717247ad5fad199c25f91271a8b41c7',
//            'type' => 'get-history',
//            'conversationId' => '5',
//            'page' => 1
//        ];


       $message = [
           'token' => '67ea6c250717247ad5fad199c25f91271a8b41c7',
           'type' => 'get-conversations',
       ];

        $message = json_encode($message);
        return Events::onMessage(1,$message);
        */
        $message_data = [
            'user_id' => 7,
            'type' => 'accept_order',
            'data' => [
                'keyword1' => trans('task.take_order.order_status.accepted'),
                'keyword2' => trans('task.take_order.be_accepted')
            ],
        ];
        app(MessageService::class)->sendMessage($message_data);


    }
    public function collectFormId(Request $request)
    {
        $rule = [
            'form_id' => 'required',
        ];

        validateParameter($rule);

        $user = User::tokenAuth();
        $form_id = $request->form_id;

        FormId::create([
            'user_id' => $user->id,
            'form_id' => $form_id,
            'open_id' => $user->open_id,
        ]);

        throw new RequestSuccessException();
    }
}
