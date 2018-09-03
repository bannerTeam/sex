<?php
namespace app\common\model;
use think\Db;
use think\Cache;
use app\common\util\Pinyin;

class Role extends Base {
    // 设置数据表（不含前缀）
    protected $name = 'role';

    // 定义时间戳字段名
    protected $createTime = '';
    protected $updateTime = '';

    // 自动完成
    protected $auto       = [];
    protected $insert     = [];
    protected $update     = [];

    public function getRoleStatusTextAttr($val,$data)
    {
        $arr = [0=>'禁用',1=>'启用'];
        return $arr[$data['role_status']];
    }

    public function countData($where)
    {
        $total = $this->where($where)->count();
        return $total;
    }

    public function listData($where,$order,$page=1,$limit=20,$start=0,$field='*',$addition=1,$totalshow=1)
    {
        if(!is_array($where)){
            $where = json_decode($where,true);
        }
        $limit_str = ($limit * ($page-1) + $start) .",".$limit;
        if($totalshow==1) {
            $total = $this->where($where)->count();
        }
        $list = Db::name('Role')->field($field)->where($where)->order($order)->limit($limit_str)->select();
        $vod_list=[];
        if($addition==1){
            $vod_ids=[];
            foreach($list as $k=>$v){
                $vod_ids[$v['role_rid']] = $v['role_rid'];
            }
            $where2=[];
            $where2['vod_id'] = ['in', implode(',',$vod_ids)];
            $tmp_list = Db::name('Vod')->field('vod_id,vod_name,vod_en,type_id,type_id_1')->where($where2)->select();
            foreach($tmp_list as $k=>$v){
                $vod_list[$v['vod_id']] = $v;
            }
        }
        foreach($list as $k=>$v){
            if($addition==1){
                $list[$k]['data'] = $vod_list[$v['role_rid']];
            }
        }
        return ['code'=>1,'msg'=>'数据列表','page'=>$page,'pagecount'=>ceil($total/$limit),'limit'=>$limit,'total'=>$total,'list'=>$list];
    }

    public function listCacheData($lp)
    {
        if (!is_array($lp)) {
            $lp = json_decode($lp, true);
        }

        $order = $lp['order'];
        $by = $lp['by'];
        $type = $lp['type'];
        $ids = $lp['ids'];
        $paging = $lp['paging'];
        $pageurl = $lp['pageurl'];
        $level = $lp['level'];
        $wd = $lp['wd'];
        $rid = $lp['rid'];
        $letter = $lp['letter'];
        $start = intval(abs($lp['start']));
        $num = intval(abs($lp['num']));
        $half = intval(abs($lp['half']));
        $page = 1;
        $where = [];
        $totalshow=0;

        if(empty($num)){
            $num = 20;
        }
        if($start>1){
            $start--;
        }
        if(!in_array($paging, ['yes', 'no'])) {
            $paging = 'no';
        }
        $param = mac_param_url();
        if($paging=='yes') {
            $totalshow = 1;
            if(!empty($param['id'])) {
                $type = intval($param['id']);
            }
            if(!empty($param['rid'])) {
                $tid = intval($param['rid']);
            }
            if(!empty($param['level'])) {
                $level = intval($param['level']);
            }
            if(!empty($param['letter'])) {
                $letter = $param['letter'];
            }
            if(!empty($param['wd'])) {
                $wd = $param['wd'];
            }
            if(!empty($param['by'])){
                $by = $param['by'];
            }
            if(!empty($param['order'])){
                $order = $param['order'];
            }
            if(!empty($param['page'])){
                $page = intval($param['page']);
            }
            foreach($param as $k=>$v){
                if(empty($v)){
                    unset($param[$k]);
                }
            }
            if(!in_array($pageurl,['role/index','role/search'])){
                $pageurl = 'role/index';
            }
            $param['page'] = 'PAGELINK';
            $pageurl = url($pageurl,$param);

        }

        $where['role_status'] = ['eq',1];
        if(!empty($level)) {
            $where['role_level'] = ['in',$level];
        }
        if(!empty($ids)) {
            if($ids!='all'){
                $where['role_id'] = ['in',$ids];
            }
        }
        if(!empty($letter)){
            if($letter=='0-9'){
                $letter='0,1,2,3,4,5,6,7,8,9';
            }
            $where['role_letter'] = ['in',explode(',',$letter)];
        }
        if(!empty($type)) {

        }
        if(!empty($rid)) {
            $where['role_rid'] = ['eq',$tid];
        }
        if(!empty($wd)) {
            $where['role_name|role_en'] = ['like', '%' . $wd . '%'];
        }

        if(!in_array($by, ['id', 'time','time_add','score','hits','hits_day','hits_week','hits_month','up','down','level','rnd'])) {
            $by = 'time';
        }
        if(!in_array($order, ['asc', 'desc'])) {
            $order = 'desc';
        }
        $order= 'role_'.$by .' ' . $order;
        $cach_name = md5('role_listcache_'.http_build_query($where).'_'.$order.'_'.$page.'_'.$num.'_'.$start.'_'.$pageurl);

        $res = Cache::get($cach_name);
        if($GLOBALS['config']['app']['cache_core']==0 || empty($res)) {
            $res = $this->listData($where,$order,$page,$num,$start,'*',1,$totalshow);
            Cache::set($cach_name,$res,$GLOBALS['config']['app']['cache_time']);
        }
        $res['pageurl'] = $pageurl;
        $res['half'] = $half;
        return $res;
    }

    public function infoData($where,$field='*',$cache=0)
    {
        if(empty($where) || !is_array($where)){
            return ['code'=>1001,'msg'=>'参数错误'];
        }
        $key = 'role_detail_'.$where['role_id'][1].$where['role_en'][1];
        $info = Cache::get($key);
        if($GLOBALS['config']['app']['cache_core']==0 || $cache==0 || empty($info)) {
            $info = $this->field($field)->where($where)->find();
            if (empty($info)) {
                return ['code' => 1002, 'msg' => '获取数据失败'];
            }
            $info = $info->toArray();
            Cache::set($key,$info);
        }
        return ['code'=>1,'msg'=>'获取成功','info'=>$info];
    }

    public function saveData($data)
    {
        $validate = \think\Loader::validate('Role');
        if(!$validate->check($data)){
            return ['code'=>1001,'msg'=>'参数错误：'.$validate->getError() ];
        }

        $key = 'role_detail_'.$data['role_id'];
        Cache::rm($key);
        $key = 'role_detail_'.$data['role_en'];
        Cache::rm($key);

        if(empty($data['role_en'])){
            $data['role_en'] = Pinyin::get($data['role_name']);
        }

        if(empty($data['role_letter'])){
            $data['role_letter'] = strtoupper(substr($data['role_en'],0,1));
        }

        if(!empty($data['role_id'])){
            if($data['uptime']==1){
                $data['role_time'] = time();
            }
            else{
                unset($data['uptime']);
            }
            $where=[];
            $where['role_id'] = ['eq',$data['role_id']];
            $res = $this->allowField(true)->where($where)->update($data);
        }
        else{
            unset($data['uptime']);
            $data['role_time_add'] = time();
            $data['role_time'] = time();
            $res = $this->allowField(true)->insert($data);
        }
        if(false === $res){
            return ['code'=>1002,'msg'=>'保存失败：'.$this->getError() ];
        }
        return ['code'=>1,'msg'=>'保存成功'];
    }

    public function delData($where)
    {
        $res = $this->where($where)->delete();
        if($res===false){
            return ['code'=>1001,'msg'=>'删除失败：'.$this->getError() ];
        }
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
            return ['code'=>1001,'msg'=>'设置失败：'.$this->getError() ];
        }

        $list = $this->field('role_id,role_name,role_en')->where($where)->select();
        foreach($list as $k=>$v){
            $key = 'role_detail_'.$v['role_id'];
            Cache::rm($key);
            $key = 'role_detail_'.$v['role_en'];
            Cache::rm($key);
        }

        return ['code'=>1,'msg'=>'设置成功'];
    }

}