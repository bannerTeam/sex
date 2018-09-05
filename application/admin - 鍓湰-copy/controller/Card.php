<?php
namespace app\admin\controller;
use think\Db;

class Card extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $param = input();
        $param['page'] = intval($param['page']) <1 ? 1 : $param['page'];
        $param['limit'] = intval($param['limit']) <1 ? $this->_pagesize : $param['limit'];

        $where=[];
        if(in_array($param['sale_status'],['0','1'],true)){
            $where['card_sale_status'] = ['eq',$param['sale_status']];
        }
        if(in_array($param['use_status'],['0','1'],true)){
            $where['card_use_status'] = ['eq',$param['use_status']];
        }
        if(!empty($param['wd'])){
            $where['card_no'] = ['like','%'.$param['wd'].'%'];
        }


        $order='card_id desc';
        $res = model('Card')->listData($where,$order,$param['page'],$param['limit']);

        $this->assign('list',$res['list']);
        $this->assign('total',$res['total']);
        $this->assign('page',$res['page']);
        $this->assign('limit',$res['limit']);

        $param['page'] = '{page}';
        $param['limit'] = '{limit}';
        $this->assign('param',$param);
        $this->assign('title','充值卡管理');
        return $this->fetch('admin@card/index');
    }

    public function info()
    {
        if (Request()->isPost()) {
            $param = input('post.');

            if(empty($param['num']) || empty($param['money']) || empty($param['point']) ){
                return $this->error('参数错误');
            }

            $res = model('Card')->saveAllData(intval($param['num']),intval($param['money']),intval($param['point']));
            if($res['code']>1){
                return $this->error($res['msg']);
            }
            return $this->success($res['msg']);
        }


        $id = input('id');
        $where=[];
        $where['card_id'] = ['eq',$id];
        $res = model('Card')->infoData($where);

        $this->assign('info',$res['info']);

        return $this->fetch('admin@card/info');
    }

    public function del()
    {
        $param = input();
        $ids = $param['ids'];
        $all = $param['all'];

        if(!empty($ids)){
            $where=[];
            $where['card_id'] = ['in',$ids];
            if($all==1){
                $where['card_id'] = ['gt',0];
            }

            $res = model('Card')->delData($where);
            if($res['code']>1){
                return $this->error($res['msg']);
            }
            return $this->success($res['msg']);
        }
        return $this->error('参数错误');
    }

}
