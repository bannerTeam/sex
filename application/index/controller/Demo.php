<?php
namespace app\index\controller;
use think\Controller;
use think\Config;

class Demo extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

     public function ckplay()
    {
       
        return $this->fetch('demo/ckplay');
    }

}
