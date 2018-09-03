<?php
namespace app\index\controller;
use think\Controller;

class Role extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->label_role_index();
        return $this->fetch('role/index');
    }

    public function search()
    {
        $param = mac_param_url();
        $this->check_search($param);
        $this->assign('param',$param);
        return $this->fetch('role/search');
    }

    public function detail()
    {
        $info = $this->label_role_detail();
        return $this->fetch('role/detail');
    }

    public function ajax_detail()
    {
        $info = $this->label_role_detail();
        return $this->fetch('role/ajax_detail');
    }

}
