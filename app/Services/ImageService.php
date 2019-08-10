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
    public function uploadImages($files, $usage,$is_thumb = 1)
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
            if($is_thumb)
            {
                $thumbs_url[$i] = $this->request->getBasePath().'/' .$usage.'/thumb/'.$imageName;
                image_png_size_add(storage_path().$img,storage_path().$thumb);
            }
            $i++;
        }

        $image_url = implode(',',$imgs_url);
        if($is_thumb)
        {
            $thumb_img_url = implode(',',$thumbs_url);
            return [
                'image_url' => $image_url,
                'thumb_img_url'=> $thumb_img_url,
            ];
        }
        return $image_url;
    }
    public function uploadOrderImages($files,$merchant_name,$address,$is_thumb = 1)
    {
        if(is_array($files['file']))
        {
            $all_files = $files['file'];
        }
        else{
            $all_files[] = $files['file'];
        }
        isVaildImage($all_files);
        return $this->uploadOrderImagesHandle($all_files,$merchant_name,$address);
    }

    private function uploadOrderImagesHandle($files, $merchant_name,$address)
    {
        $usage = 'merchant/original';
        $watermark_usage = 'merchant/watermark';
        $directory = storage_path('uploads') . DIRECTORY_SEPARATOR . 'merchant'.DIRECTORY_SEPARATOR.'original';
        $watermark_directory = storage_path('uploads') . DIRECTORY_SEPARATOR . 'merchant'.DIRECTORY_SEPARATOR.'watermark';
        $thumb_directory = storage_path('uploads') . DIRECTORY_SEPARATOR . 'merchant'.DIRECTORY_SEPARATOR.'original'.DIRECTORY_SEPARATOR.'thumb';
        $url = '/uploads/'.$usage;
        $thumb_url = $url.'/thumb';

        //保存图片文件到服务器
        $i = 0;
        foreach ($files as $file) {
            $extension = $file->getClientOriginalExtension();
            $imageName = $merchant_name.'-'.date('YmdHis').rand(100000, 999999) . '.'.$extension ;
            $img = $url.'/'.$imageName;
            $thumb = $thumb_url.'/'.$imageName;

            Storage::put($img, file_get_contents($file->getRealPath()));

            $image_path = $directory.DIRECTORY_SEPARATOR.$imageName;
            $thumb_path = $thumb_directory.DIRECTORY_SEPARATOR.$imageName;

            image_png_size_add($image_path,$thumb_path,800,0.8);

            $new_image_path = $watermark_directory.DIRECTORY_SEPARATOR.$imageName;

            list($width, $height, $type) = getimagesize($thumb_path);

            $img = Image::make($thumb_path);

            $img->text($merchant_name, 0, $height-60,function($font) {
                $font->file(base_path('storage/uploads/font').'/'.'simsun.ttf');
                $font->size(20);
                $font->color('#ffffff');
                $font->valign('top');
                //$font->angle(45);
            });
            $img->text($address, 0, $height-40,function($font) {
                $font->file(base_path('storage/uploads/font').'/'.'simsun.ttf');
                $font->size(20);
                $font->color('#ffffff');
                $font->valign('top');
                //$font->angle(45);
            });
            $img->text(date('Y-m-d H:i:s'), 0, $height-20,function($font) {
                $font->file(base_path('storage/uploads/font').'/'.'simsun.ttf');
                $font->size(20);
                $font->color('#ffffff');
                $font->valign('top');
                //$font->angle(45);
            });
            $img->save($new_image_path);

            $imgs_url[$i] = $this->request->getBasePath().'/'.$watermark_usage.'/'.$imageName;

            $i++;
        }

        return [
            'image_url' => implode(',',$imgs_url),
        ];

    }
    public function rotate($image_path)
    {

    }

}