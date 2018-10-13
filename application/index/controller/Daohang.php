<?php
namespace app\index\controller;
use think\Controller;
use think\Cache;

class Daohang  extends Base
{
    
    /**
     * 导航
     */
    public function index()
    {
       
        return $this->fetch('daohang/index');
    }
    
    

}
