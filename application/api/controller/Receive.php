<?php
namespace app\api\controller;
use think\Controller;

class Receive extends Base
{
    var $_param;

    public function __construct()
    {
        parent::__construct();
        $this->_param = input();


        if($GLOBALS['config']['interface']['status'] != 1){
            echo json_encode(['code'=>3001,'msg'=>'接口关闭err']);
            exit;
        }
        if($GLOBALS['config']['interface']['pass'] != $this->_param['pass']){
            echo json_encode(['code'=>3002,'msg'=>'非法使用err']);
            exit;
        }
    }

    public function index()
    {

    }

    public function vod()
    {
        $info = $this->_param;

        if(empty($info['vod_name'])){
            echo json_encode(['code'=>2001,'msg'=>'名称必须err']);
            exit;
        }
        if(empty($info['type_id']) && empty($info['type_name'])){
            echo json_encode(['code'=>2002,'msg'=>'分类名称和分类id至少填写1项err']);
            exit;
        }

        $inter = mac_interface_type();
        if(empty($info['type_id'])) {
            $info['type_id'] = $inter['vodtype'][$info['type_name']];
        }

        $data['data'][] = $info;
        $res = model('Collect')->vod_data([],$data,0);
        echo json_encode($res);
    }

    public function art()
    {
        $info = $this->_param;

        if(empty($info['art_name'])){
            echo json_encode(['code'=>2001,'msg'=>'名称必须err']);
            exit;
        }
        if(empty($info['type_id']) && empty($info['type_name'])){
            echo json_encode(['code'=>2002,'msg'=>'分类名称和分类id至少填写1项err']);
            exit;
        }

        $inter = mac_interface_type();
        if(empty($info['type_id'])) {
            $info['type_id'] = $inter['arttype'][$info['type_name']];
        }
        $data['data'][] = $info;
        $res = model('Collect')->art_data([],$data,0);
        echo json_encode($res);
    }

}
