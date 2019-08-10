<?php

namespace App\Helpers\Common;

class Tree
{
    public function sort($arr,$cols='sort')
    {
        $sort = [];
        //子分类排序
        foreach ($arr as $k => &$v) {
            if(!empty($v['sub'])){
                $v['sub']=self::sort($v['sub'],$cols);
            }
            $sort[$k]=$v[$cols];
        }
        if($sort)
            array_multisort($sort,SORT_DESC,$arr);
        return $arr;
    }
    public function vTree($arr,$parent_id=0){
        $data = [];
        foreach($arr as $k => $v){
            if($v['parent_id'] == $parent_id){
                $data[$v['id']]=$v;
                $data[$v['id']] = $this->vTree($v,$v['id']);
            }
        }
        return isset($data)?$data:array();
    }
    public function getTree($data, $repeat = '&nbsp;&nbsp;', $id = 0, $new_arr = [], $number = 0){
        $child = $this->getChild($data,$id);
        if(is_array($child)){
            $number++;
            foreach($child as $id => $value){
                $level_sign = $number > 0 ? str_repeat($repeat,($number-1) * 4 ) : '';
                $value['name'] = $level_sign.$value['name'];
                $new_arr[] = $value;
                $new_arr = $this->getTree($data, $repeat, $value['id'], $new_arr,$number);
            }
        }
        return $new_arr;
    }
    /**
     * 得到子级数组
     * @param int
     * @return array
     */
    public function getChild($data, $id){
        $a = $new_arr = array();
        if(is_array($data)){
            foreach($data as $key => $a){
                if($a['parent_id'] == $id) $new_arr[$key] = $a;
            }
        }
        return $new_arr ? $new_arr : false;
    }
}