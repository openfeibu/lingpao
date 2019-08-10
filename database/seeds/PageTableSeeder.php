<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class PageTableSeeder extends Seeder
{
    public function run()
    {
        for ($i = 99;$i <= 119;$i++)
        {
        DB::table('pages')->insert([

            [
                'id'               => $i,
                'category_id'      => 18,
                'name'             => '广州飞步信息科技',
                'slug'             => 'home',
                'heading'          => '广州飞步信息科技',
                'title'            => '广州飞步信息科技',
                'content'          => '<p><br/><img src=\"http://www.clhweb.com/upload/2017/1225/index.png\" alt=\"index-byclh\"/><img src=\"/ueditor/php/upload/image/20180612/1528792215888912.png\" alt=\"four-dimensions-byclh\"/><img src=\"/ueditor/php/upload/image/20180612/1528792216804593.png\" alt=\"product-list-byclh\"/></p>',
                'meta_title'       => '广州飞步信息科技',
                'meta_keyword'     => '广州飞步信息科技',
                'meta_description' => '广州飞步信息科技',
                'image'            => '/page/page/2018/06/12/082839193/image/chowtaifook-mini-banner.png',
                'images'           => null,
                'abstract'         => null,
                'order'            => 0,
                'banner'           => null,
                'view'             => 'page',
                'compile'          => null,
                'status'           => 1,
                'recommend_type'   => 'home',
            ],

        ]);
        }
    }
}
