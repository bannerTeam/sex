<?php
namespace app\index\controller;
use think\Controller;
use \think\Request;

class Payment extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function notify()
    {
        if (Request()->isPost()) {
            $param = input();
            $pay_type = $param['pay_type'];

            if ($GLOBALS['config']['pay'][$pay_type]['appid'] == '') {
                echo '该支付选项未开启';
                exit;
            }
            //跳转到相应页面
            model('Pay' . $pay_type)->notify();
        }
        else{
            return $this->success('支付完成', url('user/index') );
        }
    }
}
