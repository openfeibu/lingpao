<?php

namespace App\Events\GatewayWorker;

use GatewayWorker\Lib\Gateway;
use Workerman\Autoloader;
use Log,DB;
use App\Models\Chat;
use App\Models\Room;
use App\Models\User;

class Events
{

    public static function onWorkerStart($businessWorker)
    {
        echo "BusinessWorker    Start\n";
    }

    public static function onConnect($client_id)
    {
        Gateway::sendToClient($client_id, json_encode(['type' => 'init', 'client_id' => $client_id]));
    }

    public static function onWebSocketConnect($client_id, $data)
    {

    }

    public static function onMessage($client_id, $message)
    {
        $response = ['code' => 0, 'message' => 'ok'];
        $message = json_decode($message);

        if (!isset($message->type)) {
            $response['message'] = 'missing parameter mode';
            $response['code'] = ERROR_CHAT;
            Gateway::sendToClient($client_id, json_encode($response));
            return false;
        }
        $user = User::getUserByToken($message->token);
		//$user = DB::name('users')->where('token',$message->token)->first();
        if(!$user)
        {
            $response['message'] = 'Authentication failure';
            $response['code'] = ERROR_CHAT;
            Gateway::sendToClient($client_id, json_encode($response));
            return false;
        }
        $user_id = $user->id;
        $user->client_id = $client_id;
        $user->save();
        $to_client_id = 0;

        switch ($message->type) {
            case 'heart':
                break;
            case 'say':   #处理发送的聊天
                /*
                if (self::authentication($message->order_id, $message->user_id)) {
                    OrderChat::store($message->order_id, $message->type, $message->content, $message->user_id);
                } else {
                    $response['msg'] = 'Authentication failure';
                    $response['errcode'] = ERROR_CHAT;
                }
                */
                break;
            case 'get-conversations':  #获取聊天列表
                //$chats = OrderChat::where('order_id', $message->order_id)->get();
                $user_id = $user->id;
                $rooms = Room::join('users as to_users','to_users.id','=','rooms.to_user_id')
                    ->join('users as from_users','from_users.id','=','rooms.from_user_id')
                    ->where('rooms.to_user_id',$user->id)
                    ->orWhere('rooms.from_user_id',$user->id)
                    ->orderBy('rooms.updated_at','desc')
                    ->select('rooms.id','rooms.from_user_id','rooms.to_user_id','rooms.updated_at')
                    ->get()
                    ->toArray();
                $conversations = [];

                foreach ($rooms as $key => $room)
                {
                    //聊天对象id
                    if($room['from_user_id'] == $user->id)
                    {
                        $to_user_id = 'to_user_id';
                    }else{
                        $to_user_id = 'from_user_id';
                    }
                    $to_user = User::where('id',$room[$to_user_id])->first(['id as to_user_id','nickname','avatar_url','gender','role']);
                    $unread = Chat::where('room_id',$room['id'])->where('to_user_id',$user->id)->where('unread',1)->count();

                    $latestMsg = Chat::where(function($query) use ($room){
                        $query->where('to_user_id',$room['to_user_id'])->where('from_user_id',$room['from_user_id']);
                    })->orWhere(function($query) use ($room){
                        $query->where('from_user_id',$room['to_user_id'])->where('to_user_id',$room['from_user_id']);
                    })->where('type','text')->orderBy('id','desc')->first();
                    $latestMsg = $latestMsg ? [
                        'type' => $latestMsg->type,
                        'content' => $latestMsg->content,
                        'timestamp' => strtotime($latestMsg->updated_at),
                        'timeStr' => friendly_date($latestMsg->updated_at),
                    ] : [];
                    $conversations[$key] = [
                        'conversationId' => $room['id'],
                        'friendId' => $to_user->to_user_id,
                        'friendHeadUrl' => $to_user->avatar_url,
                        'friendName' => $to_user->nickname,
                        'latestMsg' => $latestMsg,
                        'msgUserId' => $user->id,
                        'timeStr' => friendly_date($room['updated_at']),
                        'timestamp' => strtotime($room['updated_at']),
                        'last_time' => $room['updated_at'],
                        'unread' => $unread,
                    ];
                }
                $response['type'] = $message->type;
                $response['conversations'] = $conversations;
                break;
            case 'text':
                self::chat($message,$user);
                break;
            case 'text':
                self::chat($message,$user);
                break;
            case 'get-history':
                $room_id = $conversationId = $message->conversationId;
                $room = Room::where('id',$room_id)->first();
                Chat::where('room_id',$room_id)->where('to_user_id',$user_id)->update(['unread' => 0]);

                if($room->to_user_id == $user_id)
                {
                    $friendId = $room->from_user_id;
                }else{
                    $friendId = $room->to_user_id;
                }
                $page = $message->page;
                $per_num = 10;
                $skip=($page-1)<0?0:($page-1)*$per_num;
                $chats = Chat::where('room_id',$room_id)
                    ->skip($skip)
                    ->limit($per_num)
                    ->orderBy('id','desc')
                    ->get(['id','from_user_id','to_user_id','type','content','updated_at'])
                    ->toArray();

                $friend = User::where('id',$friendId)->first(config('model.user.user.other_visible'));
                $from_user_data = [
                    'id' => $user_id,
                    'avatar_url' => $user->avatar_url,
                    'nickname' => $user->nickname,
                    'role' => $user->role
                ];
                $to_user_data = [
                    'id' => $friend->id,
                    'avatar_url' => $friend->avatar_url,
                    'nickname' => $friend->nickname,
                    'role' => $friend->role
                ];

                foreach ($chats as $key => $chat)
                {
                    if($chat['type'] == 'image')
                    {
                        $chats[$key]['content'] = url('/image/original/'.$chat['content']);
                    }
                    $chats[$key]['timeStr'] = friendly_date($chat['updated_at']);
                    $chats[$key]['timestamp'] = strtotime($chat['updated_at']);
                }
                $response['from_user'] = $from_user_data;
                $response['to_user'] = $to_user_data;
                $response['history'] = $chats;
                $response['type'] = $message->type;
                break;
            case 'unread':
                $unread = Chat::where('to_user_id',$user->id)->where('unread',1)->count();
                $response['type'] = $message->type;
                $response['unread'] = $unread;
                break;
            case 'image':

                break;
            default:
                $response['code'] = ERROR_CHAT;
                $response['message'] = 'Undefined';
        }

        Gateway::sendToClient($client_id, json_encode($response));

    }

    public static function chat($message,$user)
    {
        $room_id = $conversationId = $message->conversationId;
        $to_user_id = $friendId = $message->friendId;
        $from_user_id = $userId = $user->id;
        $content = $message->content;
        $room = Room::where(function($query) use ($to_user_id,$from_user_id){
            $query->where('to_user_id',$to_user_id)->where('from_user_id',$from_user_id);
        })->orWhere(function($query) use ($to_user_id,$from_user_id){
            $query->where('from_user_id',$to_user_id)->where('to_user_id',$from_user_id);
        })->first();

        if(!$room)
        {
            $room = Room::create([
                'from_user_id' => $from_user_id,
                'to_user_id' => $to_user_id,
            ]);
        }else{
            Room::where('id',$room->id)->update(['updated_at' => date('Y-m-d H:i:s')]);
        }

        $chat = Chat::create([
            'from_user_id' => $from_user_id,
            'to_user_id' => $to_user_id,
            'type' => $message->type,
            'content' => $content,
            'unread' => 1,
            'room_id' => $room->id,
        ]);
        $to_client_id = User::where('id',$to_user_id)->value('client_id');
        if($to_client_id)
        {
            switch ($message->type)
            {
                case 'text':
                    $content = $content;
                    break;
                case 'image':
                    $content = url('/image/original/'.$chat['content']);
                    break;
            }
            $to_response = [
                'code' => 0,
                'message' => 'ok',
                'type' => $message->type,
                'content' => $content,
                'msgUserId' => $from_user_id,
                'to_user_id' => $to_user_id,
                'from_user_id' => $from_user_id,
                'timestamp' => strtotime($chat->updated_at),
                'timeStr' => friendly_date($chat->updated_at)
            ];
            Gateway::sendToClient($to_client_id, json_encode($to_response));
        }
    }
    public static function onClose($client_id)
    {
        Log::info('close connection' . $client_id);
    }

    private static function authentication($order_id, $user_id): bool
    {
        $order = Order::find($order_id);
        if (is_null($order)) {
            return false;
        }
        return in_array($user_id, [$order->user_id, $order->to_user_id]) ? true : false;   #判断属不属于这个订单的两个人
    }
}