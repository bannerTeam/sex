<?php
namespace app\common\model;
use think\Db;
use think\Cache;
use app\common\util\Pinyin;

class AdvGroup extends Base {
    // 设置数据表（不含前缀）
    protected $name = 'adv_group';

    // 定义时间戳字段名
    protected $createTime = '';
    protected $updateTime = '';

    // 自动完成
    protected $auto       = [];
    protected $insert     = [];
    protected $update     = [];


    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }

    public function listData($where,$order='id desc',$format='def',$mid=0,$limit=999,$start=0,$totalshow=1)
    {
        if(!is_array($where)){
            $where = json_decode($where,true);
        }
        $limit_str = ($limit * (1-1) + $start) .",".$limit;
        if($totalshow==1) {
            $total = $this->where($where)->count();
        }
        else{

        }
        $list = Db::name('adv_group')->where($where)->order($order)->limit($limit_str)->select();
           
        

        return ['code'=>1,'msg'=>'数据列表','total'=>$total,'list'=>$list];
    }


    public function infoData($where,$field='*')
    {
        if(empty($where) || !is_array($where)){
            return ['code'=>1001,'msg'=>'参数错误'];
        }
        $info = $this->field($field)->where($where)->find();

        if(empty($info)){
            return ['code'=>1002,'msg'=>'获取数据失败'];
        }
        $info = $info->toArray();

        if(!empty($info['type_extend'])){
            $info['type_extend'] = json_decode($info['type_extend'],true);
        }
        else{
            $info['type_extend'] = json_decode('{"type":"","area":"","lang":"","year":"","star":"","director":"","state":"","version":""}',true);
        }


        return ['code'=>1,'msg'=>'获取成功','info'=>$info];
    }

    public function saveData($data)
    {
        $validate = \think\Loader::validate('Type');
        if(!$validate->check($data)){
            return ['code'=>1001,'msg'=>'参数错误：'.$validate->getError() ];
        }

        if(!empty($data['type_extend'])){
            $data['type_extend'] = json_encode($data['type_extend']);
        }
        if(empty($data['type_en'])){
            $data['type_en'] = Pinyin::get($data['type_name']);
        }

        if(!empty($data['type_id'])){
            $where=[];
            $where['type_id'] = ['eq',$data['type_id']];
            $res = $this->allowField(true)->where($where)->update($data);
        }
        else{
            $res = $this->allowField(true)->insert($data);
        }
        if(false === $res){
            return ['code'=>1002,'msg'=>'保存失败：'.$this->getError() ];
        }

        $this->setCache();
        return ['code'=>1,'msg'=>'保存成功'];
    }

    public function delData($where)
    {
        $res = $this->where($where)->delete();
        if($res===false){
            return ['code'=>1001,'msg'=>'删除失败：'.$this->getError() ];
        }

        $this->setCache();
        return ['code'=>1,'msg'=>'删除成功'];
    }

    public function fieldData($where,$col,$val)
    {
        if(!isset($col) || !isset($val)){
            return ['code'=>1001,'msg'=>'参数错误'];
        }

        $data = [];
        $data[$col] = $val;

        $res = $this->allowField(true)->where($where)->update($data);

        if($res===false){
            return ['code'=>1002,'msg'=>'设置失败：'.$this->getError() ];
        }

        $this->setCache();
        return ['code'=>1,'msg'=>'设置成功'];
    }

    public function setCache()
    {
        $res = $this->listData([],'type_sort asc');
        $list = $res['list'];
        Cache::set('type_list',$list);

        $type_tree = mac_list_to_tree($list,'type_id','type_pid');
        Cache::set('type_tree',$type_tree);
    }

    public function getCache($flag='type_list')
    {
        $cache = Cache::get($flag);
        if(empty($cache)){
            $this->setCache();
            $cache = Cache::get($flag);
        }
        return $cache;
    }

    public function getCacheInfo($id)
    {
        $type_list = $this->getCache('type_list');
        if(is_numeric($id)) {
            return $type_list[$id];
        }
        else{

            foreach($type_list as $k=>$v){
                if($v['type_en'] == $id){
                    return $type_list[$k];
                }
            }
        }
    }



}