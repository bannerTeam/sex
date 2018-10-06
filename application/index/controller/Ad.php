<?php
namespace app\index\controller;
use think\Controller;
use think\Cache;

class Ad extends Base
{
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 获取广告
     * @return \think\response\Json
     */
    public function get_adv($flag)
    {
        $datas = Cache::get('config_ad');
        if(empty($datas)){
            $datas = config('ad');
            Cache::set('config_ad',$datas,60*60*12);
        }        
        
        $list = $datas['list'];        
        $res = array();        
        for ($i = 0; $i < count($list); $i++) {
            if($list[$i]['flag'] == $flag && intval($list[$i]['status']) === 1){
                $res[] = $list[$i];
            }
        }        
        
        return $res;
    }
    
    

}
