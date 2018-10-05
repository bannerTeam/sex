<?php
namespace app\index\controller;
use think\Model;
use think\Db;

class Index extends Base
{
    public function index()
    {        
//         header('Location: /index.php/vod/show/page/1/id/1.html');
//         exit;
//         $param = mac_param_url();

        return $this->fetch( 'index/index');
    }

    public function wap_index()
    {
        $param = mac_param_url();
        return $this->fetch( 'index/index');
    }

}
