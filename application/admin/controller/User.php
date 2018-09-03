<?php
namespace app\admin\controller;
use think\Db;

class User extends Base
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
        if(in_array($param['status'],['0','1'],true)){
            $where['user_status'] = $param['status'];
        }
        if(!empty($param['group'])){
            $where['group_id'] = $param['group'];
        }
        if(!empty($param['wd'])){
            $where['user_name'] = ['like','%'.$param['wd'].'%'];
        }

        $order='user_id desc';
        $res = model('User')->listData($where,$order,$param['page'],$param['limit']);

        $group_list = model('Group')->getCache('group_list');
        foreach($res['list'] as $k=>$v){
            $res['list'][$k]['group_name'] = $group_list[$v['group_id']]['group_name'];
        }

        $this->assign('list',$res['list']);
        $this->assign('total',$res['total']);
        $this->assign('page',$res['page']);
        $this->assign('limit',$res['limit']);

        $param['page'] = '{page}';
        $param['limit'] = '{limit}';
        $this->assign('param',$param);

        $this->assign('group_list',$group_list);

        $this->assign('title','会员管理');
        return $this->fetch('admin@user/index');
    }

    public function info()
    {
        if (Request()->isPost()) {
            $param = input('post.');
            $res = model('User')->saveData($param);
            if($res['code']>1){
                return $this->error($res['msg']);
            }
            return $this->success($res['msg']);
        }

        $id = input('id');
        $where=[];
        $where['user_id'] = ['eq',$id];
        $res = model('User')->infoData($where);

        $this->assign('info',$res['info']);

        $order='group_id asc';
        $where=[];
        $res = model('Group')->listData($where,$order);
        $this->assign('group_list',$res['list']);

        $this->assign('title','会员信息');
        return $this->fetch('admin@user/info');
    }

    public function del()
    {
        $param = input();
        $ids = $param['ids'];

        if(!empty($ids)){
            $where=[];
            $where['user_id'] = ['in',$ids];
            $res = model('User')->delData($where);
            if($res['code']>1){
                return $this->error($res['msg']);
            }
            return $this->success($res['msg']);
        }
        return $this->error('参数错误');
    }

    public function field()
    {
        $param = input();
        $ids = $param['ids'];
        $col = $param['col'];
        $val = $param['val'];

        if(!empty($ids) && in_array($col,['user_status']) && in_array($val,['0','1'])){
            $where=[];
            $where['user_id'] = ['in',$ids];

            $res = model('User')->fieldData($where,$col,$val);
            if($res['code']>1){
                return $this->error($res['msg']);
            }
            return $this->success($res['msg']);
        }
        return $this->error('参数错误');
    }



}
