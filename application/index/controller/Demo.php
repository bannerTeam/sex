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
    
    public function ckplayer()
    {
        return $this->fetch('demo/ckplayer');
    }
    
    public function video()
    {
        return $this->fetch('demo/video');
    }
    
    public function collect_ds()
    {
        
        return $this->fetch('demo/collect_ds');
    }
    
    public function collect_union()
    {
        
        return $this->fetch('demo/collect_union');
    }
}
