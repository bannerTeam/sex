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
        
        
        $filename = './application/extra/ad.php';
        if (is_writable($filename)) {
            echo 'The file is writable';
        } else {
            echo 'The file is not writable';
        }
        exit;
        
        
        $expression = config('adv');
        
        $this->assign('adv',urldecode(json_encode($expression)));
        
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
