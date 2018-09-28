<?php
namespace app\index\controller;
use think\Controller;

class Adv extends Base
{
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 获取广告
     * @return \think\response\Json
     */
    public function get_adv()
    {
    	   
        $res = file_get_contents('./data/adv.txt');
        
        
        return json(json_decode($res));
    }
    
    /**
     * 广告显示的时候调用
     * @return \think\response\Json
     */
    public function exhibitionMonitor()
    {
        
        $res['info'] = '成功';
        $res['status'] = '1';
        
        
        return json($res);
    }
    
    /**
     * 点击广告的调用
     * @return \think\response\Json
     */
    public function clickMonitor()
    {
        $res['info'] = '成功';
        $res['status'] = '1';
        
        return json(($res));
    }
     /**
     * 获取广告
     * @return \think\response\Json
     */
    public function get_index_adv()
    {
    	   
        $res = config('adv');
        
        //urldecode(json_encode($expression))
        
        return json($res);
    }

}
