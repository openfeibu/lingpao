<?php
namespace App\Services;

use App\Helpers\ErrorCode;
use GuzzleHttp\Client;
use App\Exceptions\OutputServerMessageException;

class AmapService
{
    public function __construct()
    {
        $this->key = config('amap.web_key');
        $this->client = new Client();
    }
    public function geocode_regeo($location)
    {
        $url = "https://restapi.amap.com/v3/geocode/regeo?key=".$this->key."&location=".$location.'&output=JSON';
        //$res = $this->client->request("GET", $url);
        $res = $this->client->get($url);
        $data = json_decode($res->getBody()->getContents(),true);

        if(!$data['status'])
        {
            throw new \App\Exceptions\OutputServerMessageException('请求失败');
        }

        return $data;
    }

}