<?php
namespace app\admin\controller;
use think\Cache;

class Ad extends Base
{
    var $_pre;
    public function __construct()
    {
        parent::__construct();
        $this->_pre = 'ad';

    }

    public function index()
    {
        $datas = config($this->_pre);
        
        $this->assign('group',$datas['group']);
        
        $this->assign('list',$datas['list']);
       
        
        $this->assign('title','广告管理');
        return $this->fetch('admin@ad/index');
    }

    public function info()
    {
        $param = input();
        $datas = config($this->_pre);
        $list = $datas['list'];
        
        if (Request()->isPost()) {            
                 
            //下标
            $index = $param['id'];  
            unset($param['id']);
            
            if(empty($param['sort'])){
                $param['sort'] = 99;
            }
            
            
            if(is_numeric($index) && $list[$index-1]){
                $list[$index-1] = $param;
            }else{
                $list[] = $param;
            }
            
            
            $sort=[];
            foreach ($list as $k=>&$v){
                $sort[] = $v['sort'];
            }
            array_multisort($sort, SORT_ASC, SORT_NUMERIC , $list);
            
            $datas['list'] = $list;
            $res = mac_arr2file( APP_PATH .'extra/'.$this->_pre.'.php', $datas);
            if($res===false){
                return $this->error('保存失败，请重试!');
            }
            Cache::set('config_ad',null);
            return $this->success('保存成功!');
        }
        
        $this->assign('group',$datas['group']);
        
        $index = $param['id'];
        if(is_numeric($index)){
            $info = $list[$index-1];
        }
        
        
        $this->assign('id',$info ? ($index-1):'');
        
        $this->assign('info',$info);
        $this->assign('title','广告信息管理');
        return $this->fetch('admin@ad/info');
    }

    public function del()
    {
        $param = input();
        $datas = config($this->_pre);
        $list = $datas['list'];       
        $index = $param['ids'];
        if(!is_numeric($index)){
            return $this->error('删除失败，请重试!');
        }
        
        unset($list[$index - 1]);
        
        $sort=[];
        foreach ($list as $k=>&$v){
            $sort[] = $v['sort'];
        }
        array_multisort($sort, SORT_ASC, SORT_NUMERIC , $list);
        
        $datas['list'] = $list;
        $res = mac_arr2file(APP_PATH. 'extra/'.$this->_pre.'.php', $datas);
        if($res===false){
            return $this->error('删除失败，请重试!');
        }
        
        Cache::set('config_ad',null);
        return $this->success('删除成功!');
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
