<?php

namespace App\Services;

use Log;
use File;
use Storage;
use Illuminate\Http\Request;
use Route,Auth,Hash,Input,Image;
use Intervention\Image\ImageManager;

class ImageService
{
    protected $request;

    function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * 上传图片
     * 注意：用户必须是已登录状态
     *
     * @param  file $files  要上传的图片文件
     * @param  string $usage 图片的用途，并将上传的图片文件存放到public/uploads/$usage中
     *
     * @return array        图片链接
     */
    public function uploadImages($files, $usage,$is_thumb = 0)
    {
        if(is_array($files['file']))
        {
            $all_files = $files['file'];
        }
        else{
            $all_files[] = $files['file'];
        }
        isVaildImage($all_files);
        return $this->uploadImagesHandle($all_files,$usage,$is_thumb);
    }

    private function uploadImagesHandle($files, $usage='common',$is_thumb = 1)
    {

        //如果文件夹不存在，则创建文件夹
        $directory = storage_path('uploads') . DIRECTORY_SEPARATOR . $usage;
        $url = '/uploads/'.$usage;
        $thumb_url = $url.'/thumb';
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
            if($is_thumb)
            {
                $thumb_directory = $directory . DIRECTORY_SEPARATOR . 'thumb';
                File::makeDirectory($thumb_directory, 0755, true);
            }
        }

        //保存图片文件到服务器
        $i = 0;
        foreach ($files as $file) {
            $extension = $file->getClientOriginalExtension();
            $imageName = time().rand(100000, 999999) . '.' . $extension;
            $img = $url.'/'.$imageName;
            $thumb = $thumb_url.'/'.$imageName;

            Storage::put($img, file_get_contents($file->getRealPath()));

            $images_url[$i]['img_url'] = $this->request->getBasePath().'/'.$usage.'/'.$imageName;
            $images_url[$i]['usage'] = $usage;
            $images_url[$i]['created_at'] = date("Y-m-d H:i:s");

            $imgs_url[$i] = $images_url[$i]['img_url'];
            $imgs_url_full[$i] = url('/image/original/'.$imgs_url[$i] );
            if($is_thumb)
            {
                $thumbs_url[$i] = $this->request->getBasePath().'/' .$usage.'/thumb/'.$imageName;
                $thumbs_url_full[$i] = url('/image/original/'.$thumbs_url[$i] );
                image_png_size_add(storage_path().$img,storage_path().$thumb);
            }
            $i++;
        }

        //$image_url = implode(',',$imgs_url);
        if($is_thumb)
        {
            //$thumb_img_url = implode(',',$thumbs_url);
            return [
                'image_url' => $imgs_url,
                'image_url_full' => $imgs_url_full,
                'thumb_url'=> $thumbs_url,
                'thumb_url_full'=> $thumbs_url_full,
            ];
        }
        return [
            'image_url' => $imgs_url,
            'image_url_full' => $imgs_url_full,
        ];

    }


}