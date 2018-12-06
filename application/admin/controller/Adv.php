<?php
namespace app\admin\controller;
use think\Cache;

class Adv extends Base
{
    var $_pre;
    public function __construct()
    {
        parent::__construct();
        $this->_pre = 'adv';

    }

    public function index()
    {
        
        
        $where=[];
        $res = model('Adv')->listData($where);        
        $this->assign('list',$res['list']);
        $this->assign('total',$res['total']);
       
        
        $res = model('AdvGroup')->listData($where);   
        $this->assign('group',$res['list']);
               
        
        $this->assign('title','广告管理');
        return $this->fetch('admin@adv/index');
    }

    public function info()
    {
        
       
        $param = input();
        
        $adv_id = intval($param['id']);
        
        if (Request()->isPost()) {            
                 
            $param = input('post.');
            $res = model('Adv')->saveData($param);
            if($res['code'] > 1){
                return $this->error('保存失败!'.$res['msg']);
            }
            
            return $this->success('保存成功!');
        }
        
      
        $this->assign('id',$adv_id);
        
        $where['id'] = $adv_id;
        $res = model('Adv')->infoData($where);
        
        $info =  $res['info'];
        if($info['start_time']){
            $info['start_time'] = date('Y-m-d',$info['start_time']);
        }
        
        if($info['end_time']){
            $info['end_time'] = date('Y-m-d',$info['end_time']);
        }
        
        $this->assign('info',$info);
        
        $where=[];
        $res = model('AdvGroup')->listData($where);
        $this->assign('group',$res['list']);
        
                
        
        $this->assign('title','广告信息管理');
        return $this->fetch('admin@adv/info');
    }

    public function del()
    {
        $param = input();
        
        $adv_id = intval($param['id']);
        if(!is_numeric($adv_id)){
            return $this->error('删除失败，请重试!');
        }
        
        $where['id'] = $adv_id;
        $res = model('Adv')->delData($where);        
        
        if($res['code'] === 1){
            return $this->success('删除成功!');
        }
        return $this->error('删除失败，请重试');
               
    }

    public function field()
    {
        $param = input();
        $ids = $param['ids'];
        $col = $param['col'];
        $val = $param['val'];

        if(!empty($ids) && in_array($col,['parse_status','status'])){
            $list = config($this->_pre);

            foreach($list as $k=>&$v){
                $v[$col] = $val;
            }
            $res = mac_arr2file(APP_PATH. 'extra/'.$this->_pre.'.php', $list);
            if($res===false){
                return $this->error('保存失败，请重试!');
            }
            return $this->success('保存成功!');
        }
        return $this->error('参数错误');
    }

}
