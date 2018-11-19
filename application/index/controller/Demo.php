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

    public function index(){
                
        
        header("Cache-Control: no-store, no-cache, must-revalidate");//强制不缓存
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");//禁止本页被缓存
        
        
        return $this->fetch('demo/index');
    }
    
     public function ckplay()
    {
        return $this->fetch('demo/ckplay');
    }
    
    public function ckplay520()
    {
        return $this->fetch('demo/ckplay520');
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
    
     public function submit()
    {
        
        return $this->fetch('demo/submit');
    }
    public function ajax(){
    	return $this->fetch('demo/ajax');
    }
}
