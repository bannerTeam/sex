<?php
namespace app\index\controller;
use think\Controller;

class Vod extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return $this->fetch('vod/index');
    }

    public function type()
    {
        $info = $this->label_type();
        return $this->fetch( mac_tpl_fetch('vod',$info['type_tpl'],'type') );
    }

    public function show()
    {
        //var_dump(111);
        //$info = $this->label_type();
        //var_dump(mac_tpl_fetch('vod',$info['type_tpl_list'],'show') );
        //$t = mac_tpl_fetch('vod',$info['type_tpl_list'],'show');
        $tpl = 'vod/show';
        //var_dump($vo);
        return $this->fetch( $tpl );
    }

    public function ajax_show()
    {
        $info = $this->label_type();
        return $this->fetch('vod/ajax_show');
    }

    public function search()
    {
        $param = mac_param_url();
        $this->check_search($param);
        $this->assign('param',$param);
        return $this->fetch('vod/search');
    }

    public function detail()
    {
        $info = $this->label_vod_detail();
        return $this->fetch( mac_tpl_fetch('vod',$info['vod_tpl'],'detail') );
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
    
    

}
