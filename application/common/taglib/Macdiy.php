<?php
namespace app\common\taglib;
use think\template\TagLib;
use think\Db;

class Macdiy extends Taglib {

	protected $tags = [
        'test'=> ['attr'=>'order,by,num'],
    ];

    public function tagTest($tag,$content)
    {
        $order = $tag['order'];
        $by = $tag['by'];
        $num = $tag['num'];

        echo $order;die;
    }
}
