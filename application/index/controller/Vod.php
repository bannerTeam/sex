<?php
namespace app\index\controller;
use think\Controller;
use think\Request;

class Vod extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    private function assign_param(){
        
        $param = mac_param_url();
        $by = $param['by'];
        if(empty($by)){
            $by = 'time';
        }
        $this->assign('sort',$by);
        
        $timeadd = $param['timeadd'];
        if(empty($by)){
            $timeadd = 0;
        }
        
        $typeid = $param['id'];
        if(empty($typeid)){
            $type = '';
        }
        $this->assign('typeid',$typeid);
        
        $wd = $param['wd'];
        if(empty($wd)){
            $wd = '';
        }
        $this->assign('wd',$wd);
        
        $this->assign('timeadd',$timeadd);
        
        return $param;
    }
    public function index()
    {
        $param = $this->assign_param();
        
        $this->assign('vodindex','1');
        
        
        if($param['timeadd']  == 1){
            $this->assign('title','今日更新');
        }else if($param['timeadd']  == 7){
            $this->assign('title','发现');
        }
        
        return $this->fetch('vod/index');
    }

    public function type()
    {        
       
        $this->assign_param();
        
        $info = $this->label_type();      
           
        $this->assign('title',$info['type_name']);
        
        return $this->fetch( mac_tpl_fetch('vod',$info['type_tpl'],'type') );
    }

    public function show()
    {
        $info = $this->label_type();
    
        $this->assign('obj',$info);       
        
        $tpl = 'vod/show';
      
        return $this->fetch( $tpl );
    }

    public function ajax_show()
    {
        $info = $this->label_type();
        return $this->fetch('vod/ajax_show');
    }

    public function search()
    {
        $param = $this->assign_param();        
        
        $this->check_search($param);
        $this->assign('param',$param);
        
        $this->assign('title',$param['wd']);
        
        return $this->fetch('vod/search');
    }

    public function detail()
    {
        $info = $this->label_vod_detail();
        return $this->fetch( mac_tpl_fetch('vod',$info['vod_tpl'],'detail') );
        
        $info = $this->label_vod_play('play');
        
        return $this->fetch( mac_tpl_fetch('vod',$info['vod_tpl_play'],'play') );
    }

    public function ajax_detail()
    {
        $info = $this->label_vod_detail();
        return $this->fetch('vod/ajax_detail');
    }

    public function role()
    {
        $info = $this->label_vod_role();
        return $this->fetch('vod/role');
    }

    public function play()
    {
        
        $this->assign('videoplay',true);
        $info = $this->label_vod_play('play');        
                
        return $this->fetch( mac_tpl_fetch('vod',$info['vod_tpl_play'],'play') );
    }

    public function down()
    {
        $info = $this->label_vod_play('down');
        return $this->fetch( mac_tpl_fetch('vod',$info['vod_tpl_down'],'down') );
    }

    public function player()
    {
        $info = $this->label_vod_play('play',[],0,1);
        return $this->fetch();
    }

    public function rss()
    {
        $info = $this->label_vod_detail();
        return $this->fetch('vod/rss');
    }
    
    /**
     * 获取影片 的 上一部，下一部
     */
    public function ajax_front_after(){
        
        if (Request::instance()->isAjax()){
            
            $res['code'] = 0;
            $res['front'] = '';
            $res['after'] = '';
            
            $input = input() ;
            
            $vod_id = intval($input['id']);
            
            //获取上一条
            $where['vod_id'] = ['<',$vod_id];
            $front = model('Vod')->findData($where,'vod_id');
            if($front['code'] === 1){
                $res['front'] = $front['info']['vod_id'];
            }
            
            //获取下一条
            $where['vod_id'] = ['>',$vod_id];
            $after = model('Vod')->findData($where,'vod_id','vod_id asc');
            if($after['code'] === 1){
                $res['after'] = $after['info']['vod_id'];
            }
            
            return json($res);
            
        }
        
        
        
    }
   
    

}
