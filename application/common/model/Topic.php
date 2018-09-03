<?php
namespace app\common\model;
use think\Db;
use think\Cache;
use app\common\util\Pinyin;

class Topic extends Base {
    // 设置数据表（不含前缀）
    protected $name = 'topic';

    // 定义时间戳字段名
    protected $createTime = '';
    protected $updateTime = '';
    protected $autoWriteTimestamp = true;

    // 自动完成
    protected $auto       = [];
    protected $insert     = [];
    protected $update     = [];

    public function getTopicStatusTextAttr($val,$data)
    {
        $arr = [0=>'禁用',1=>'启用'];
        return $arr[$data['topic_status']];
    }

    public function countData($where)
    {
        $total = $this->where($where)->count();
        return $total;
    }

    public function listData($where,$order,$page=1,$limit=20,$start=0,$field='*',$totalshow=1)
    {
        if(!is_array($where)){
            $where = json_decode($where,true);
        }
        $limit_str = ($limit * ($page-1) + $start) .",".$limit;
        if($totalshow==1) {
            $total = $this->where($where)->count();
        }
        $tmp = Db::name('Topic')->where($where)->order($order)->limit($limit_str)->select();

        $list = [];
        foreach($tmp as $k=>$v){
            $list[$v['topic_id']] = $v;
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
        $ids = $lp['ids'];
        $paging = $lp['paging'];
        $pageurl = $lp['pageurl'];
        $level = $lp['level'];
        $letter = $lp['letter'];
        $tag = $lp['tag'];
        $start = intval(abs($lp['start']));
        $num = intval(abs($lp['num']));
        $half = intval(abs($lp['half']));
        $page = 1;
        $where = [];
        $totalshow = 0;

        if(empty($num)){
            $num = 20;
        }
        if($start>1){
            $start--;
        }

        if(!in_array($paging, ['yes', 'no'])) {
            $paging = 'no';
        }

        if($paging=='yes') {
            $totalshow = 1;
            $param = mac_param_url();
            if (!empty($param['id'])) {
                $ids = intval($param['id']);
            }
            if(!empty($param['level'])) {
                $level = intval($param['level']);
            }
            if(!empty($param['letter'])) {
                $letter = intval($param['letter']);
            }
            if(!empty($param['wd'])) {
                $wd = $param['wd'];
            }
            if(!empty($param['tag'])) {
                $tag = $param['tag'];
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
            $pageurl = mac_url_topic_index($param);

        }

        $where['topic_status'] = ['eq',1];
        if(!empty($level)) {
            $where['topic_level'] = ['in',$level];
        }
        if(!empty($ids)) {
            if($ids!='all'){
                $where['topic_id'] = ['in',$ids];
            }
        }
        if(!empty($letter)){
            $where['topic_letter'] = ['eq',$letter];
        }
        if(!empty($wd)) {
            $where['topic_name|topic_en|topic_sub'] = ['like', '%' . $wd . '%'];
        }
        if(!empty($tag)) {
            $where['topic_tag'] = ['like', '%' . $tag . '%'];
        }

        if(!in_array($by, ['id', 'time','time_add','score','hits','hits_day','hits_week','hits_month','up','down','level','rnd'])) {
            $by = 'time';
        }
        if(!in_array($order, ['asc', 'desc'])) {
            $order = 'desc';
        }
        $order= 'topic_'.$by .' ' . $order;
        $cach_name = md5('topic_listcache_'.http_build_query($where).'_'.$order.'_'.$page.'_'.$num.'_'.$start.'_'.$pageurl);

        $res = Cache::get($cach_name);
        if($GLOBALS['config']['app']['cache_core']==0 || empty($res)) {
            $res = $this->listData($where,$order,$page,$num,$start,'*',$totalshow);
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
        $key = 'topic_detail_'.$where['topic_id'][1].$where['topic_en'][1];
        $info = Cache::get($key);
        if($GLOBALS['config']['app']['cache_core']==0 || $cache==0 || empty($info) || 1==1) {
            $info = $this->field($field)->where($where)->find();
            if (empty($info)) {
                return ['code' => 1002, 'msg' => '获取数据失败'];
            }
            $info = $info->toArray();
            if (!empty($info['topic_extend'])) {
                $info['topic_extend'] = json_decode($info['topic_extend'], true);
            } else {
                $info['topic_extend'] = json_decode('{"type":"","area":"","lang":"","year":"","star":"","director":"","state":"","version":""}', true);
            }
            $info['vod_list'] = [];
            $info['art_list'] = [];

            if (!empty($info['topic_rel_vod'])) {
                $where = [];
                $where['vod_id'] = ['in', $info['topic_rel_vod']];
                $where['vod_status'] = ['eq', 1];
                $order = 'vod_time desc';
                $field = '*';
                $res = model('Vod')->listData($where, $order, 1, 999, 0, $field);
                if ($res['code'] == 1) {
                    $info['vod_list'] = $res['list'];
                }
            }
            if (!empty($info['topic_rel_art'])) {
                $where = [];
                $where['art_id'] = ['in', $info['topic_rel_art']];
                $where['art_status'] = ['eq', 1];
                $order = 'art_time desc';
                $field = '*';
                $res = model('Art')->listData($where, $order, 1, 999, 0, $field);
                if ($res['code'] == 1) {
                    $info['art_list'] = $res['list'];
                }
            }

            if (!empty($info['topic_tag'])) {
                $tmp = explode(',',$info['topic_tag']);
                foreach($tmp as $k=>$v){
                    if(!empty($v)) {
                        $tmp[$k] = '%' . $v . '%';
                    }
                    else{
                        unset($k);
                    }
                }
                $where=[];
                $where['vod_tag'] = ['like',$tmp,'OR'];
                $where['vod_status'] = ['eq', 1];
                $order = 'vod_time desc';
                $field = '*';
                $res = model('Vod')->listData($where, $order, 1, 999, 0, $field);
                if ($res['code'] == 1) {
                    $info['vod_list'] = $res['list'];
                }

                $where=[];
                $where['art_tag'] = ['like',$tmp,'OR'];
                $where['art_status'] = ['eq', 1];
                $order = 'art_time desc';
                $field = '*';
                $res = model('Art')->listData($where, $order, 1, 999, 0, $field);
                if ($res['code'] == 1) {
                    $info['art_list'] = $res['list'];
                }
            }

            Cache::set($key,$info);
        }
        return ['code'=>1,'msg'=>'获取成功','info'=>$info];
    }

    public function saveData($data)
    {
        $validate = \think\Loader::validate('Topic');
        if(!$validate->check($data)){
            return ['code'=>1001,'msg'=>'参数错误：'.$validate->getError() ];
        }
        $key = 'topic_detail_'.$data['topic_id'];
        Cache::rm($key);
        $key = 'topic_detail_'.$data['topic_en'];
        Cache::rm($key);

        if(empty($data['topic_extend'])){
            $data['topic_extend'] = '';
        }
        if(empty($data['topic_en'])){
            $data['topic_en'] = Pinyin::get($data['topic_name']);
        }

        $data['topic_time'] = time();
        if(!empty($data['topic_id'])){
            $where=[];
            $where['topic_id'] = ['eq',$data['topic_id']];
            $res = $this->allowField(true)->where($where)->update($data);
        }
        else{
            $data['topic_time_add'] = time();
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
            return ['code'=>1002,'msg'=>'设置失败：'.$this->getError() ];
        }

        $list = $this->field('topic_id,topic_name,topic_en')->where($where)->select();
        foreach($list as $k=>$v){
            $key = 'topic_detail_'.$v['topic_id'];
            Cache::rm($key);
            $key = 'topic_detail_'.$v['topic_en'];
            Cache::rm($key);
        }

        return ['code'=>1,'msg'=>'设置成功'];
    }



}