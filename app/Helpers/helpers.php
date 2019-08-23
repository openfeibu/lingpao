<?php

use Illuminate\Support\Facades\Request;
use App\Facades\Hashids;
use App\Facades\Trans;
use Illuminate\Support\Facades\Validator;

if (!function_exists('hashids_encode')) {
    /**
     * Encode the given id.
     *
     * @param int/array $id
     *
     * @return string
     */
    function hashids_encode($idorarray)
    {
        return Hashids::encode($idorarray);
    }

}

if (!function_exists('hashids_decode')) {
    /**
     * Decode the given value.
     *
     * @param string $value
     *
     * @return array / int
     */
    function hashids_decode($value)
    {
        $return = Hashids::decode($value);

        if (empty($return)) {
            return null;
        }

        if (count($return) == 1) {
            return $return[0];
        }

        return $return;
    }

}

if (!function_exists('folder_new')) {
    /**
     * Get new upload folder pathes.
     *
     * @param string $prefix
     * @param string $sufix
     *
     * @return array
     */
    function folder_new($prefix = null, $sufix = null)
    {
        $folder        = date('Y/m/d/His') . rand(100, 999);
        return $folder;
    }
}

if (!function_exists('blade_compile')) {
    /**
     * Get new upload folder pathes.
     *
     * @param string $prefix
     * @param string $sufix
     *
     * @return array
     */
    function blade_compile($string, array $args = [])
    {
        $compiled = \Blade::compileString($string);
        ob_start() and extract($args, EXTR_SKIP);

        // We'll include the view contents for parsing within a catcher

        // so we can avoid any WSOD errors. If an exception occurs we
        // will throw it out to the exception handler.
        try
        {
            eval('?>' . $compiled);
        }

            // If we caught an exception, we'll silently flush the output

            // buffer so that no partially rendered views get thrown out
            // to the client and confuse the user with junk.
        catch (\Exception $e) {
            ob_get_clean();throw $e;
        }

        $content = ob_get_clean();
        $content = str_replace(['@param  ', '@return  ', '@var  ', '@throws  '], ['@param ', '@return ', '@var ', '@throws '], $content);

        return $content;

    }

}


if (!function_exists('trans_url')) {
    /**
     * Get translated url.
     *
     * @param string $url
     *
     * @return string
     */
    function trans_url($url)
    {
        return Trans::to($url);
    }

}

if (!function_exists('trans_dir')) {
    /**
     * Return the direction of current language.
     *
     * @return string (ltr|rtl)
     *
     */
    function trans_dir()
    {
        return Trans::getCurrentTransDirection();
    }

}

if (!function_exists('trans_setlocale')) {
    /**
     * Set local for the translation
     *
     * @param string $locale
     *
     * @return string
     */
    function trans_setlocale($locale = null)
    {
        return Trans::setLocale($locale);
    }

}

if (!function_exists('checkbox_array')) {
    /**
     * Convert array to use in form check box
     *
     * @param array $array
     * @param string $name
     * @param array $options
     *
     * @return array
     */
    function checkbox_array(array $array, $name, $options = [])
    {
        $return = [];

        foreach ($array as $key => $val) {
            $return[$val] = array_merge(['name' => "{$name}[{$key}]"], $options);
        }

        return $return;
    }

}

if (!function_exists('pager_array')) {
    /**
     * Return request values to be used in paginator
     *
     * @return array
     */
    function pager_array()
    {

        return Request::only(
            config('database.criteria.params.search', 'search'),
            config('database.criteria.params.searchFields', 'searchFields'),
            config('database.criteria.params.columns', 'columns'),
            config('database.criteria.params.sortBy', 'sortBy'),
            config('database.criteria.params.orderBy', 'orderBy'),
            config('database.criteria.params.with', 'with')
        );
    }

}

if (!function_exists('user_type')) {
    /**
     * Get user id.
     *
     * @param string $guard
     *
     * @return int
     */
    function user_type($guard = null)
    {
        $guard = is_null($guard) ? getenv('guard') : $guard;
        $provider = config("auth.guards." . $guard . ".provider", 'users');
        return config("auth.providers.$provider.model", App\User::class);
    }

}

if (!function_exists('user_id')) {
    /**
     * Get user id.
     *
     * @param string $guard
     *
     * @return int
     */
    function user_id($guard = null)
    {

        $guard = is_null($guard) ? getenv('guard') : $guard;

        if (Auth::guard($guard)->check()) {
            return Auth::guard($guard)->user()->id;
        }
        return null;
    }

}

if (!function_exists('get_guard')) {
    /**
     * Return thr property of the guard for current request.
     *
     * @param string $property
     *
     * @return mixed
     */
    function get_guard($property = 'guard')
    {
        switch ($property) {
            case 'url':
                return empty(getenv('guard')) ? 'user' : current(explode(".", getenv('guard')));
                break;
            case 'route':
                return empty(getenv('guard')) ? 'user' : current(explode(".", getenv('guard')));
                break;
            case 'model':
                $provider = config("auth.guards." . getenv('guard') . ".provider", 'users');
                return config("auth.providers.$provider.model", App\Models\User::class);
                break;
            default:
                return getenv('guard');
        }
    }

}

if (!function_exists('guard_url')) {
    /**
     * Return thr property of the guard for current request.
     *
     * @param string $property
     *
     * @return mixed
     */
    function guard_url($url, $translate = true)
    {
        $prefix = empty(getenv('guard')) ? 'user' : current(explode(".", getenv('guard')));
        if ($translate){
            return trans_url($prefix . '/' . $url);
        }
        return $prefix . '/' . $url;
    }

}

if (!function_exists('set_route_guard')) {
    /**
     * Set local for the translation
     *
     * @param string $sub
     * @param string $guard
     * @param string $theme
     * @return string
     */
    function set_route_guard($sub='web', $guard=null,$theme=null)
    {
        $i = ($sub == 'web') ? 1 : 2;
        $theme ? set_theme($theme) : '';
        //check whether guard is the first parameter of the route
        $guard = is_null($guard) ? request()->segment($i) : $guard;

        if (!empty(config("auth.guards.$guard"))){
            putenv("guard={$guard}.{$sub}");
            app('auth')->shouldUse("{$guard}.{$sub}");
            return $guard;
        }

        //check whether guard is the second parameter of the route
        $guard = is_null($guard) ? request()->segment(++$i) : $guard;
        if (!empty(config("auth.guards.$guard"))){
            putenv("guard={$guard}.{$sub}");
            app('auth')->shouldUse("{$guard}.{$sub}");
            return $guard;
        }

        putenv("guard=client.{$sub}");
        app('auth')->shouldUse("client.{$sub}");
        return $sub;
    }

}
if(!function_exists('set_theme'))
{
    function set_theme($theme = '')
    {
        if(!empty($theme))
        {
            putenv("theme={$theme}");
        }
    }
}


if (!function_exists('users')) {
    /**
     * Get upload folder.
     *
     * @param string $folder
     *
     * @return string
     */
    function users($property, $guard = null)
    {
        $guard = is_null($guard) ? getenv('guard') : $guard;

        if (Auth::guard($guard)->check()) {
            return Auth::guard($guard)->user()->$property;
        }
        return null;
    }

}

if (!function_exists('user')) {
    /**
     * Return the user model
     * @param type|null $guard
     * @return type
     */
    function user($guard = null)
    {
        $guard = is_null($guard) ? getenv('guard') : $guard;
        if (Auth::guard($guard)->check()) {
            return Auth::guard($guard)->user();
        }

        return null;
    }

}

if (!function_exists('user_check')) {
    /**
     * Check whether user is logged in
     * @param type|null $guard
     * @return type
     */
    function user_check($guard = null)
    {
        $guard = is_null($guard) ? getenv('guard') : $guard;
        return Auth::guard($guard)->check();
    }

}

if (!function_exists('format_date')) {
    /**
     * Format date
     *
     * @param string $date
     * @param string $format
     *
     * @return date
     */
    function format_date($date, $format = 'd M Y')
    {
        if (empty($date)) return null;
        return date($format, strtotime($date));
    }

}

if (!function_exists('format_date_time')) {
    /**
     * Format datetime
     *
     * @param date $datetime
     * @param string $format
     *
     * @return datetime
     */
    function format_date_time($datetime, $format = 'd M Y h:i A')
    {
        return date($format, strtotime($datetime));
    }

}

if (!function_exists('format_time')) {
    /**
     * Format time.
     *
     * @param string $time
     * @param string $format
     *
     * @return time
     */
    function format_time($time, $format = 'h:i A')
    {
        return date($format, strtotime($time));
    }

}
if (!function_exists('theme_asset')) {
    /**
     * Get translated url.
     *
     * @param string $url
     *
     * @return string
     */
    function theme_asset($file)
    {
        return app('theme')->asset()->url($file);
    }
}
if (!function_exists('replace_image_url')) {
    function replace_image_url($content,$url)
    {
        if($url)
        {
            preg_match_all("/<img(.*)src=\"([^\"]+)\"[^>]+>/isU", $content, $matches);
            $img = "";
            if(!empty($matches)) {
                $img = $matches[2];
            }
            if(!empty($img))
            {
                $patterns= array();
                $replacements = array();
                foreach($img as $imgItem){
                    if(strpos($imgItem,'http') === false)
                    {
                        $final_imgUrl = $url.$imgItem;
                        $replacements[] = $final_imgUrl;
                        $img_new = "/".preg_replace("/\//i","\/",$imgItem)."/";
                        $patterns[] = $img_new;
                    }
                }
                ksort($patterns);
                ksort($replacements);
                $vote_content = preg_replace($patterns, $replacements, $content);
                return $vote_content;
            } else {
                return $content;
            }
        } else {
            return $content;
        }
    }
}
if (!function_exists('get_substr')) {
    function get_substr($str, $len = 12, $dot = true)
    {
        $i = 0;
        $l = 0;
        $c = 0;
        $a = array();
        while ($l < $len) {
            $t = substr($str, $i, 1);
            if (ord($t) >= 224) {
                $c = 3;
                $t = substr($str, $i, $c);
                $l += 2;
            } elseif (ord($t) >= 192) {
                $c = 2;
                $t = substr($str, $i, $c);
                $l += 2;
            } else {
                $c = 1;
                $l++;
            }
            $i += $c;
            if ($l > $len) break;
            $a[] = $t;
        }
        $re = implode('', $a);
        if (substr($str, $i, 1) !== false) {
            array_pop($a);
            ($c == 1) and array_pop($a);
            $re = implode('', $a);
            $dot and $re .= '...';
        }
        return $re;
    }
}
if (!function_exists('handle_image_url')) {
    function handle_image_url($image_url = '', $host = '')
    {
        $host = $host ? $host : config('app.image_url') . '/';
        $host .= 'image/original';
        if (!empty($image_url) && strpos($image_url, 'http') === false) {
            $image_url = $host . $image_url;
        }
        return $image_url;
    }
}
function handle_images_thumb($usage,$images, $host = '')
{

    foreach ($images as $key => $image) {
        $image = str_replace($usage,$usage.'/thumb',$image);
        $images[$key] = handle_image_url($image, $host);
    }
    return $images;
}
if (!function_exists('first_image')) {
    function first_image($content)
    {
        $data['content'] = $content;
        $soContent = $data['content'];
        $soImages = '~<img [^>]* />~';
        preg_match_all($soImages, $soContent, $thePics);
        $allPics = count($thePics[0]);
        preg_match('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|PNG))\"?.+>/i', $thePics[0][0], $match);
        $data['ig'] = $thePics[0][0];
        if ($allPics > 0) {
            return $match[1];
        } else {
            return null;
        }
    }
}
if (!function_exists('list_image_url_absolute')) {
    function list_image_url_absolute($list, $size = 'sm')
    {
        foreach ($list as $key => $data) {
            $list[$key]['image'] = image_url_absolute($data['image'], $size);
        }
        return $list;
    }
}
if (!function_exists('image_url_absolute')) {
    function image_url_absolute($image, $size = 'sm')
    {
        return $image ? url("/image/" . $size . $image) : '';
    }
}
if (!function_exists('handle_images')) {
    function handle_images($images, $host = '')
    {
        if(!array_filter($images))
        {
            return [];
        }
        foreach ($images as $key => $image) {
            $images[$key] = handle_image_url($image, $host);
        }
        return count($images) ? $images : [];
    }
}
if (!function_exists('setting')) {
    function setting($slug, $value = 'value')
    {
        return \App\Models\Setting::where('slug', $slug)->value($value);
    }
}
if (!function_exists('logo')) {
    function logo()
    {
        $logo =  \App\Models\Setting::where('slug', 'logo')->value('value');
        return url('/image/original/'.$logo);
    }
}
if (!function_exists('page')) {
    function page($slug, $value = 'content')
    {
        return \App\Models\Page::where('slug', $slug)->value($value);
    }
}
if (!function_exists('date_html')) {
    function date_html($date)
    {
        $month = date('M',strtotime($date));
        $day = date('d',strtotime($date));
        $html = '<div class="date"><p>'.$day.'</p><span>'.$month.'</span></div>';
        return $html;
    }
}
/*
* ============================== 截取含有 html标签的字符串 =========================
* @param (string) $str   待截取字符串
* @param (int)  $lenth  截取长度
* @param (string) $repalce 超出的内容用$repalce替换之（该参数可以为带有html标签的字符串）
* @param (string) $anchor 截取锚点，如果截取过程中遇到这个标记锚点就截至该锚点处
* @return (string) $result 返回值
* @demo  $res = cut_html_str($str, 256, '...'); //截取256个长度，其余部分用'...'替换
* ===============================================================================
*/
if (!function_exists('cut_html_str')) {
    function cut_html_str($str, $lenth, $replace = '......', $anchor = '<!-- break -->')
    {
        $_lenth = mb_strlen($str, "utf-8"); // 统计字符串长度（中、英文都算一个字符）
        if ($_lenth <= $lenth) {
            return $str;    // 传入的字符串长度小于截取长度，原样返回
        }
        $strlen_var = strlen($str);     // 统计字符串长度（UTF8编码下-中文算3个字符，英文算一个字符）
        if (strpos($str, '<') === false) {
            return mb_substr($str, 0, $lenth);  // 不包含 html 标签 ，直接截取
        }
        if ($e = strpos($str, $anchor)) {
            return mb_substr($str, 0, $e);  // 包含截断标志，优先
        }
        $html_tag = 0;  // html 代码标记
        $result = '';   // 摘要字符串
        $html_array = array('left' => array(), 'right' => array()); //记录截取后字符串内出现的 html 标签，开始=>left,结束=>right
        /*
        * 如字符串为：<h3><p><b>a</b></h3>，假设p未闭合，数组则为：array('left'=>array('h3','p','b'), 'right'=>'b','h3');
        * 仅补全 html 标签，<? <% 等其它语言标记，会产生不可预知结果
        */
        for ($i = 0; $i < $strlen_var; ++$i) {
            if (!$lenth) break;  // 遍历完之后跳出
            $current_var = substr($str, $i, 1); // 当前字符
            if ($current_var == '<') { // html 代码开始
                $html_tag = 1;
                $html_array_str = '';
            } else if ($html_tag == 1) { // 一段 html 代码结束
                if ($current_var == '>') {
                    $html_array_str = trim($html_array_str); //去除首尾空格，如 <br / > < img src="" / > 等可能出现首尾空格
                    if (substr($html_array_str, -1) != '/') { //判断最后一个字符是否为 /，若是，则标签已闭合，不记录
                        // 判断第一个字符是否 /，若是，则放在 right 单元
                        $f = substr($html_array_str, 0, 1);
                        if ($f == '/') {
                            $html_array['right'][] = str_replace('/', '', $html_array_str); // 去掉 '/'
                        } else if ($f != '?') { // 若是?，则为 PHP 代码，跳过
                            // 若有半角空格，以空格分割，第一个单元为 html 标签。如：<h2 class="a"> <p class="a">
                            if (strpos($html_array_str, ' ') !== false) {
                                // 分割成2个单元，可能有多个空格，如：<h2 class="" id="">
                                $html_array['left'][] = strtolower(current(explode(' ', $html_array_str, 2)));
                            } else {
                                //若没有空格，整个字符串为 html 标签，如：<b> <p> 等，统一转换为小写
                                $html_array['left'][] = strtolower($html_array_str);
                            }
                        }
                    }
                    $html_array_str = ''; // 字符串重置
                    $html_tag = 0;
                } else {
                    $html_array_str .= $current_var; //将< >之间的字符组成一个字符串,用于提取 html 标签
                }
            } else {
                --$lenth; // 非 html 代码才记数
            }
            $ord_var_c = ord($str{$i});
            switch (true) {
                case (($ord_var_c & 0xE0) == 0xC0): // 2 字节
                    $result .= substr($str, $i, 2);
                    $i += 1;
                    break;
                case (($ord_var_c & 0xF0) == 0xE0): // 3 字节
                    $result .= substr($str, $i, 3);
                    $i += 2;
                    break;
                case (($ord_var_c & 0xF8) == 0xF0): // 4 字节
                    $result .= substr($str, $i, 4);
                    $i += 3;
                    break;
                case (($ord_var_c & 0xFC) == 0xF8): // 5 字节
                    $result .= substr($str, $i, 5);
                    $i += 4;
                    break;
                case (($ord_var_c & 0xFE) == 0xFC): // 6 字节
                    $result .= substr($str, $i, 6);
                    $i += 5;
                    break;
                default: // 1 字节
                    $result .= $current_var;
            }
        }
        if ($html_array['left']) { //比对左右 html 标签，不足则补全
            $html_array['left'] = array_reverse($html_array['left']); //翻转left数组，补充的顺序应与 html 出现的顺序相反
            foreach ($html_array['left'] as $index => $tag) {
                $key = array_search($tag, $html_array['right']); // 判断该标签是否出现在 right 中
                if ($key !== false) { // 出现，从 right 中删除该单元
                    unset($html_array['right'][$key]);
                } else { // 没有出现，需要补全
                    $result .= '</' . $tag . '>';
                }
            }
        }
        return $result . $replace;
    }
}
if (!function_exists('drop_blank')) {
    function drop_blank($str)
    {
        $str = preg_replace("/\t/", "", $str); //使用正则表达式替换内容，如：空格，换行，并将替换为空。
        $str = preg_replace("/\r\n/", "", $str);
        $str = preg_replace("/\r/", "", $str);
        $str = preg_replace("/\n/", "", $str);
        $str = preg_replace("/ /", "", $str);
        $str = preg_replace("/  /", "", $str);  //匹配html中的空格
        return trim($str); //返回字符串
    }
}
if (!function_exists('isVaildImage')) {
    function isVaildImage($files)
    {
        $error = '';

        foreach($files as $key => $file)
        {
            $name = $file->getClientOriginalName();
            if(!$file->isValid())
            {
                $error.= $name.$file->getErrorMessage().';';
            }
            if(!in_array( strtolower($file->extension()),config('common.img_type'))){
                $error.= $name."类型错误;";
            }
            if($file->getClientSize() > config('common.img_size')){
                $img_size = config('common.img_size')/(1024*1024);
                $error.= $name.'超过'.$img_size.'M';
            }
        }
        if($error)
        {
            throw new \App\Exceptions\OutputServerMessageException($error);
        }
    }
}
if (!function_exists('isVaildExcel')) {
    function isVaildExcel($file)
    {
        $error = '';


        $name = $file->getClientOriginalName();
        if(!$file->isValid())
        {
            $error.= $name.$file->getErrorMessage().';';
        }
//        if(!in_array( strtolower($file->extension()),config('common.excel_type'))){
//            $error.= $name."为".strtolower($file->extension())."格式，非Excel格式;";
//        }
        if($file->getClientSize() > config('common.file_size')){
            $file_size = config('common.file_size')/(1024*1024);
            $error.= $name.'超过'.$file_size.'M';
        }

        if($error)
        {
            throw new \App\Exceptions\OutputServerMessageException($error);
        }
    }
}
if (!function_exists('image_png_size_add')) {
    function image_png_size_add($imgsrc, $imgdst,$max_width=1000,$size=0.9)
    {
        list($width, $height, $type) = getimagesize($imgsrc);
        $ratio = $width > $max_width ? $max_width / $width : 1;
        $new_width = $ratio * $width * $size;
        $new_height = $ratio * $height * $size;

        switch ($type) {
            case 1:
                $giftype = check_gifcartoon($imgsrc);
                if ($giftype) {
                    $image_wp = imagecreatetruecolor($new_width, $new_height);
                    $image = imagecreatefromgif($imgsrc);
                    imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                    imagegif($image_wp, $imgdst, 75);
                    imagedestroy($image_wp);
                }
                break;
            case 2:
                $image_wp = imagecreatetruecolor($new_width, $new_height);
                $image = imagecreatefromjpeg($imgsrc);
                imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                imagejpeg($image_wp, $imgdst, 75);
                imagedestroy($image_wp);
                break;
            case 3:
                $image_wp = imagecreatetruecolor($new_width, $new_height);
                $image = imagecreatefrompng($imgsrc);
                imagesavealpha($image, true);
                imagealphablending($image_wp, false);
                imagesavealpha($image_wp, true);
                imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                imagepng($image_wp, $imgdst);
                imagedestroy($image_wp);
                break;
        }

    }
}
if (!function_exists('check_gifcartoon')) {
    function check_gifcartoon($image_file)
    {
        $fp = fopen($image_file, 'rb');
        $image_head = fread($fp, 1024);
        fclose($fp);
        return true;
    }
}
if (!function_exists('generate_token')) {
    function generate_token()
    {
        return md5(uniqid());
    }
}
if (!function_exists('avatar')) {
    function avatar($avatar)
    {
        return $avatar ? url('image/original'.$avatar) : url('image/original'.config('common.default_avatar'));
    }
}
if (!function_exists('generate_order_sn')) {
    function generate_order_sn($prefix='')
    {
        $order_sn = $prefix.date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        return $order_sn;
    }
}
if (!function_exists('is_status_desc')) {
    function is_status_desc($val)
    {
        if (!$val) {
            $val = 0;
        }
        return config('common.is_status.' . $val);
    }
}
if (!function_exists('del_dir_file')) {
    function del_dir_file($dir)
    {
        //先删除目录下的文件：
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $full_path = $dir . "/" . $file;
                if (!is_dir($full_path)) {
                    unlink($full_path);
                } else {
                    deldir($full_path);
                }
            }
        }
        closedir($dh);
    }
}
if (!function_exists('serviceFee')) {
    function serviceFee($total_fee)
    {
        return ($total_fee * 0.02) > 0.1 ? ($total_fee * 0.02) : 0.1;
    }
}

if (!function_exists('moneyHandle')) {
    function moneyHandle($money, $type = 1, $prefix = false, $suffix = false)
    {
        $money = $money;
        if ($prefix) {
            $money = "￥" . $money;
        } else if ($suffix) {
            $money .= "元";
        }
        if ($type == 1) {
            $money = '+' . $money;
        } else if ($type == -1) {
            $money = '-' . $money;
        }
        return $money;
    }
}
if (!function_exists('handlePayPassword')) {
    function handlePayPassword($pay_password)
    {
        $hash = password_hash($pay_password, PASSWORD_BCRYPT);
        return $hash;
    }
}
if (!function_exists('validateParameter')) {
    function validateParameter($rules,$message=[])
    {

        $validator = Validator::make(Request::all(), $rules,$message);
        if ($validator->fails()) {
            throw new \App\Exceptions\OutputServerMessageException($validator->errors()->first());
        } else {
            return true;
        }
    }
}
if (!function_exists('validateCustomParameter')) {
    function validateCustomParameter($data,$rules,$message=[])
    {

        $validator = Validator::make($data, $rules,$message);
        if ($validator->fails()) {
            throw new \App\Exceptions\OutputServerMessageException($validator->errors()->first());
        } else {
            return true;
        }
    }
}
if (!function_exists('validateData')) {
    function validateData($value, $custom)
    {
        if (preg_match("/[\'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/", $value)) {
            throw new \App\Exceptions\OutputServerMessageException($custom . "含非法参数");
        }
        return true;
    }
}
if (!function_exists('visible_data')) {
    function visible_data($data,$keys)
    {
        $return_data = [];
        foreach ($keys as $key)
        {
            $return_data[$key] = $data[$key];
        }
        return $return_data;
    }
}
if (!function_exists('check_urgent_price')) {
    function check_urgent_price($urgent_price)
    {
        $urgent_min_price = setting('urgent_min_price');
        if($urgent_price && $urgent_price < setting('urgent_min_price'))
        {
            throw new \App\Exceptions\OutputServerMessageException("加急费用最低".$urgent_min_price."元");
        }
    }
}
/**
 * 友好的时间显示
 *
 * @param  int    $sTime 待显示的时间
 * @param  string $type  类型. normal | mohu | full | ymd | other
 * @param  string $alt   已失效
 * @return string
 */
if(!function_exists('friendly_date')){
    function friendly_date($sTime, $type = 'mohu', $alt = 'false')
    {
        if (!$sTime) {
            return '';
        }
        //	var_dump($sTime);exit;
        $sTime = strtotime($sTime);
        //sTime=源时间，cTime=当前时间，dTime=时间差
        $cTime = time();
        $dTime = $cTime - $sTime;
        $dDay = ceil($dTime/3600/24);
        //$dDay     =   intval($dTime/3600/24);
        $dYear = intval(date('Y', $cTime)) - intval(date('Y', $sTime));
        //normal：n秒前，n分钟前，n小时前，日期
        if ($type == 'normal') {
            if ($dTime < 60) {
                if ($dTime < 10) {
                    return '刚刚';    //by yangjs
                } else {
                    return intval(floor($dTime / 10) * 10).'秒前';
                }
            } elseif ($dTime < 3600) {
                return intval($dTime / 60).'分钟前';
                //今天的数据.年份相同.日期相同.
            } elseif ($dYear == 0 && $dDay == 0) {
                //return intval($dTime/3600)."小时前";
                return '今天'.date('H:i', $sTime);
            } elseif ($dYear == 0) {
                return date('m月d日 H:i', $sTime);
            } else {
                return date('Y-m-d H:i', $sTime);
            }
        }elseif ($type == 'weixin'){
            //返回的时间
            $timeStr = "";
            //获取当前时间
            $addTime = explode(',', date('Y,n,j,w,a,h,i,y', $sTime));//年，月，日，星期，上下午，时，分
            $nowTime = explode(',', date('Y,n,j,w,a,h,i,y', $cTime));

            $dayPerMonthAddTime = getDayPerMonth($addTime[0]);
            $week = array(0=>"星期日",1=>"星期一",2=>"星期二",3=>"星期三",4=>"星期四",5=>"星期五",6=>"星期六");
            //如果时间差小于一天的,显示（上午 时间） / （下午 时间）
            if($addTime[0] == $nowTime[0] && $addTime[1] == $nowTime[1] && $addTime[2] == $nowTime[2]) {
                $timeStr .= $addTime[5] . ":" . $addTime[6];
            } else if(($addTime[0] == $nowTime[0] && $addTime[1] == $nowTime[1] && $addTime[2] == $nowTime[2]-1)
                || ($addTime[0] == $nowTime[0] && $nowTime[1]-$addTime[1] == 1 && $dayPerMonthAddTime[$addTime[1]] == $addTime[2] && $nowTime[2] == 1)
                || ($nowTime[0]-$addTime[0] == 1 && $addTime[1] == 12 && $addTime[2] == 31 && $nowTime[1] == 1 && $nowTime[2] == 1)) {
                //如果时间差在昨天,三种情况（同一月份内跨一天、月末跨越到月初、年末跨越到年初）显示格式：昨天 时:分 上午/下午
                $timeStr .= "昨天 " . $addTime[5] . ":" . $addTime[6] . " ";
            } else if(($addTime[0] == $nowTime[0] && $addTime[1] == $nowTime[1] && $nowTime[2] - $addTime[2] < 7)
                || ($addTime[0] == $nowTime[0] && $nowTime[1]-$addTime[1] == 1 && $dayPerMonthAddTime[$addTime[1]]-$addTime[2]+$nowTime[2] < 7
                    || ($nowTime[0]-$addTime[0] == 1 && $addTime[1] == 12 && $nowTime[1] == 1 && 31-$addTime[2]+$nowTime[2] < 7))) { //如果时间差在一个星期之内的,也是三种情况，显示格式：星期 时:分 上午/下午

                $timeStr .= $week[$addTime[3]] . " " . $addTime[5] . ":" . $addTime[6];
            } else { //显示格式：月/日/年 时:分 上午/下午
                $timeStr .= $addTime[1] . "/" . $addTime[2] . "/" . $addTime[7] . " " . $addTime[5] . ":" . $addTime[6];
            }

            if($addTime[4] == "am") {
                $timeStr .= " 上午";
            } else if($addTime[4] == "pm") {
                $timeStr .= " 下午";
            }

            return $timeStr;
        }elseif ($type == 'mohu') {
            if ($dTime < 60) {
                return $dTime.'秒前';
            } elseif ($dTime < 3600) {
                return intval($dTime / 60).'分钟前';
            } elseif ($dTime >= 3600 && $dTime < 3600 * 24) {
                return intval($dTime / 3600).'小时前';
            } elseif ($dDay > 0 && $dDay <= 7) {
                return intval($dDay).'天前';
            } elseif ($dDay > 7 &&  $dDay <= 30) {
                return intval($dDay / 7).'周前';
            } elseif ($dDay > 30) {
                return intval($dDay / 30).'个月前';
            }
            var_dump($dDay);exit;
            //full: Y-m-d , H:i:s
        } elseif ($type == 'full') {
            return date('Y-m-d , H:i:s', $sTime);
        } elseif ($type == 'ymd') {
            return date('Y-m-d', $sTime);
        } else {
            if ($dTime < 60) {
                return $dTime.'秒前';
            } elseif ($dTime < 3600) {
                return intval($dTime / 60).'分钟前';
            } elseif ($dTime >= 3600 && $dDay == 0) {
                return intval($dTime / 3600).'小时前';
            } elseif ($dYear == 0) {
                return date('Y-m-d H:i:s', $sTime);
            } else {
                return date('Y-m-d H:i:s', $sTime);
            }
        }
    }
}
function get_task_objective_model($objective_model)
{
    switch ($objective_model)
    {
        case "TakeOrder":
            return app(\App\Models\TakeOrder::class);
            break;
    }
}

function checkBalance($user,$price)
{
    if(!$user->is_pay_password)
    {
        throw new NotFoundPayPasswordException();
    }
    $pay_password  = Request::get('pay_password');
    if (!password_verify($pay_password, $user->pay_password)) {
        throw new \App\Exceptions\OutputServerMessageException('支付密码错误');
    }
    if($price > $user->balance){
        throw new \App\Exceptions\OutputServerMessageException('余额不足,请选择其他支付方式');
    }
}