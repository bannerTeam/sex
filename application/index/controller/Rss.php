<?php
namespace app\index\controller;
use think\Controller;

class Rss extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return $this->fetch('rss/index');
    }

    public function baidu()
    {
        return $this->fetch('rss/baidu');
    }

    public function google()
    {
        return $this->fetch('rss/google');
    }


}
