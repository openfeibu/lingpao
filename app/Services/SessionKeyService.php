<?php
namespace App\Services;

use \GuzzleHttp\Client;
use App\Helpers\Constants as Constants;

class SessionKeyService
{
    /**
     * 通过 code 换取 session key
     * @param string $code
     * @return string $session_key
     * @throws \Exception
     */
    public function getSessionKey($code)
    {
        $appId = config("weapp.appid");
        $appSecret = config("weapp.secret");
        list($session_key, $openid) = array_values($this->getSessionKeyDirectly($appId, $appSecret, $code));
        return [
            'session_key' => $session_key,
            'openid' => $openid
        ];
    }
    /**
     * 直接请求微信获取 session key
     * @param string $appId
     * @param string $appSecret
     * @param string $code
     * @return array { $session_key, $openid }
     * @throws \Exception
     */
    public function getSessionKeyDirectly($appId, $appSecret, $code)
    {
        $requestParams = [
            'appid' => $appId,
            'secret' => $appSecret,
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        ];
        $client = new Client();
        $url = config('weapp.code2session_url') . http_build_query($requestParams);
        $res = $client->request("GET", $url, [
            'timeout' => Constants::getNetworkTimeout()
        ]);
        $status = $res->getStatusCode();
        $body = json_decode($res->getBody(), true);

        if ($status !== 200 || !$body || isset($body['errcode'])) {
            throw new \App\Exceptions\OutputServerMessageException(Constants::E_LOGIN_FAILED);
        }
        return $body;
    }

}