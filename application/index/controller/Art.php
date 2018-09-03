<?php
namespace app\index\controller;
use think\Controller;

class Art extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return $this->fetch('art/index');
    }

    public function type()
    {
        $info = $this->label_type();
        return $this->fetch( mac_tpl_fetch('art',$info['type_tpl'],'type') );
    }

    public function show()
    {
        $info = $this->label_type();
        return $this->fetch( mac_tpl_fetch('art',$info['type_tpl_list'],'show') );
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
        return $this->fetch('art/search');
    }

    public function detail()
    {
        $info = $this->label_art_detail();
        return $this->fetch( mac_tpl_fetch('art',$info['art_tpl'],'detail') );
    }

    public function ajax_detail()
    {
        $info = $this->label_art_detail();
        return $this->fetch('art/ajax_detail');
    }

    public function rss()
    {
        $info = $this->label_art_detail();
        return $this->fetch('art/rss');
    }

}
