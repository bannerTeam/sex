<?php
namespace app\index\controller;
use think\Controller;

class Actor extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return $this->fetch('actor/index');
    }

    public function show()
    {
        $info = $this->label_actor();
        return $this->fetch( 'actor/show' );
    }

    public function ajax_show()
    {
        $info = $this->label_actor();
        return $this->fetch('actor/ajax_show');
    }

    public function search()
    {
        $param = mac_param_url();
        $this->check_search($param);
        $this->assign('param',$param);
        return $this->fetch('actor/search');
    }

    public function detail()
    {
        $info = $this->label_actor_detail();
        return $this->fetch( 'actor/detail' );
    }

    public function ajax_detail()
    {
        $info = $this->label_actor_detail();
        return $this->fetch('actor/ajax_detail');
    }

    public function rss()
    {
        $info = $this->label_actor_detail();
        return $this->fetch('actor/rss');
    }

}
