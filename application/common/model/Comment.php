<?php
namespace app\common\model;
use think\Db;

class Comment extends Base {
    // 设置数据表（不含前缀）
    protected $name = 'comment';

    // 定义时间戳字段名
    protected $createTime = '';
    protected $updateTime = '';

    // 自动完成
    protected $auto       = [];
    protected $insert     = [];
    protected $update     = [];

    public function getCommentStatusTextAttr($val,$data)
    {
        $arr = [0=>'禁用',1=>'启用'];
        return $arr[$data['comment_status']];
    }


    public function listData($where,$order,$page=1,$limit=20,$start=0)
    {
        if(!is_array($where)){
            $where = json_decode($where,true);
        }
        $limit_str = ($limit * ($page-1) + $start) .",".$limit;
        $total = $this->where($where)->count();
        $list = Db::name('Comment')->where($where)->order($order)->limit($limit_str)->select();

        $user_ids=[];
        foreach($list as $k=>$v){
            $list[$k]['user_portrait'] = mac_get_user_portrait($v['user_id']);

            $where2=[];
            $where2['comment_pid'] = $v['comment_id'];
            $sub = Db::name('Comment')->where($where2)->order($order)->select();
            $list[$k]['sub'] = $sub;
            foreach($sub as $k2=>$v2){
                $list[$k]['sub'][$k2]['user_portrait'] = mac_get_user_portrait($v2['user_id']);
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
        $paging = $lp['paging'];
        $start = intval(abs($lp['start']));
        $num = intval(abs($lp['num']));
        $rid = intval(abs($lp['rid']));
        $pid = intval(abs($lp['pid']));
        $mid = intval(abs($lp['mid']));
        $half = intval(abs($lp['half']));
        $page = 1;
        $page_url = '';
        $where = [];

        if(empty($num)){
            $num = 20;
        }
        if($start>1){
            $start--;
        }
        if (!in_array($mid, ['1','2','3'])) {
            $mid = 1;
        }

        if(!in_array($paging, ['yes', 'no'])) {
            $paging = 'no';
        }

        if($paging=='yes') {
            $param = mac_param_url();
            if(!empty($param['mid'])){
                $mid = $param['mid'];
            }
            if(!empty($param['rid'])){
                $rid = $param['rid'];
            }
            if(!empty($param['pid'])){
                $pid = $param['pid'];
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
            $param['page'] = 'PAGELINK';
            $pageurl = url('comment/index',$param);
        }

        $where['comment_status'] = ['eq',1];
        $where['comment_pid'] = ['eq',0];

        if(!empty($rid)){
            $where['comment_rid'] = $rid;
        }
        if(!empty($pid)){
            $where['comment_pid'] = $pid;
        }
        if(!empty($mid)){
            $where['comment_mid'] = $mid;
        }

        if(!in_array($by, ['id', 'time'])) {
            $by = 'time';
        }
        if(!in_array($order, ['asc', 'desc'])) {
            $order = 'desc';
        }
        $order= 'comment_'.$by .' ' . $order;

        $cach_name = md5('comment_listcache_'.join('&',$where).'_'.$order.'_'.$page.'_'.$num.'_'.$start);

        $res = $this->listData($where,$order,$page,$num,$start);
        $res['pageurl'] = $pageurl;
        $res['half'] = $half;
        return $res;

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

        return ['code'=>1,'msg'=>'获取成功','info'=>$info];
    }

    public function saveData($data)
    {
        $validate = \think\Loader::validate('Comment');
        if(!$validate->check($data)){
            return ['code'=>1001,'msg'=>'参数错误：'.$validate->getError() ];
        }

        if(!empty($data['comment_id'])){
            $where=[];
            $where['comment_id'] = ['eq',$data['comment_id']];
            $res = $this->allowField(true)->where($where)->update($data);
        }
        else{
            $data['comment_time'] = time();
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
        return ['code'=>1,'msg'=>'设置成功'];
    }

}