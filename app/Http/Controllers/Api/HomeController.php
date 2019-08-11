<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
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
}
