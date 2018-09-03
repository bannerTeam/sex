<?php
namespace app\common\model;
use think\Db;
use think\Cache;
use app\common\util\Pinyin;

class Art extends Base {
    // 设置数据表（不含前缀）
    protected $name = 'art';

    // 定义时间戳字段名
    protected $createTime = '';
    protected $updateTime = '';

    // 自动完成
    protected $auto       = [];
    protected $insert     = [];
    protected $update     = [];

    public function getArtStatusTextAttr($val,$data)
    {
        $arr = [0=>'禁用',1=>'启用'];
        return $arr[$data['art_status']];
    }

    public function getArtContentTextAttr($val,$data)
    {
        $arr = explode('$$$',$data['art_content']);
        return $arr;
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
        $list = Db::name('Art')->field($field)->where($where)->order($order)->limit($limit_str)->select();
        //dump($where);die;
        //echo $this->getLastSql();die;
        //分类
        $type_list = model('Type')->getCache('type_list');
        //用户组
        $group_list = model('Group')->getCache('group_list');

        foreach($list as $k=>$v){
            if($addition==1){
	            if(!empty($v['type_id'])) {
	                $list[$k]['type'] = $type_list[$v['type_id']];
                    $list[$k]['type_1'] = $type_list[$list[$k]['type']['type_pid']];
	            }
	            if(!empty($v['group_id'])) {
	                $list[$k]['group'] = $group_list[$v['group_id']];
	            }
            }
        }
        return ['code'=>1,'msg'=>'数据列表','page'=>$page,'pagecount'=>ceil($total/$limit),'limit'=>$limit,'total'=>$total,'list'=>$list];
    }

    public function listRepeatData($where,$order,$page=1,$limit=20,$start=0,$field='*',$addition=1)
    {
        if(!is_array($where)){
            $where = json_decode($where,true);
        }
        $limit_str = ($limit * ($page-1) + $start) .",".$limit;

        $total = $this
            ->join('tmpart t','t.name1 = art_name')
            ->where($where)
            ->count();

        $list = Db::name('Art')
            ->join('tmpart t','t.name1 = art_name')
            ->field($field)
            ->where($where)
            ->order($order)
            ->limit($limit_str)
            ->select();

        //dump($where);die;
        //echo $this->getLastSql();die;
        //分类
        $type_list = model('Type')->getCache('type_list');
        //用户组
        $group_list = model('Group')->getCache('group_list');

        foreach($list as $k=>$v){
            if($addition==1){
                if(!empty($v['type_id'])) {
                    $list[$k]['type'] = $type_list[$v['type_id']];
                    $list[$k]['type_1'] = $type_list[$list[$k]['type']['type_pid']];
                }
                if(!empty($v['group_id'])) {
                    $list[$k]['group'] = $group_list[$v['group_id']];
                }
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
        $tag = $lp['tag'];
        $class = $lp['class'];
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
            if(!empty($param['tid'])) {
                $tid = intval($param['tid']);
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
            if(!empty($param['tag'])) {
                $tag = $param['tag'];
            }
            if(!empty($param['class'])) {
                $class = $param['class'];
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
            if(!in_array($pageurl,['art/type','art/show','art/search'])){
                $pageurl = 'art/type';
            }
            $param['page'] = 'PAGELINK';

            if($pageurl=='art/type'){
                $type = intval( MAC_TYPE_ID );
                $type_list = model('Type')->getCache('type_list');
                $type_info = $type_list[$type];
                $pageurl = mac_url_type($type_info,$param);
            }
            else{
                $pageurl = url($pageurl,$param);
            }

        }

        $where['art_status'] = ['eq',1];
        if(!empty($level)) {
            $where['art_level'] = ['in',$level];
        }
        if(!empty($ids)) {
            if($ids!='all'){
                $where['art_id'] = ['in',$ids];
            }
        }
        if(!empty($letter)){
            if($letter=='0-9'){
                $letter='0,1,2,3,4,5,6,7,8,9';
            }
            $where['art_letter'] = ['in',explode(',',$letter)];
        }
        if(!empty($type)) {
            if($type=='current'){
                $type = intval( MAC_TYPE_ID );
                $where['art_id'] = ['not in',$param['id']];
            }
            if($type!='all') {
                $tmp_arr = explode(',', $type);
                $type_list = model('Type')->getCache('type_list');
                $type = [];
                foreach ($type_list as $k2 => $v2) {
                    if (in_array($v2['type_id'] . '', $tmp_arr) || in_array($v2['type_pid'] . '', $tmp_arr)) {
                        $type[] = $v2['type_id'];
                    }
                }
                $type = array_unique($type);
                $where['type_id'] = ['in', implode(',', $type)];
            }
        }
        if(!empty($tid)) {
            $where['type_id'] = ['eq',$tid];
        }
        if(!empty($wd)) {
            $where['art_name|art_en|art_sub'] = ['like', '%' . $wd . '%'];
        }
        if(!empty($tag)) {
            $where['art_tag'] = ['like', '%' . $tag . '%'];
        }
        if(!empty($class)) {
            $where['art_class'] = ['like', '%' . $class . '%'];
        }

        if(!in_array($by, ['id', 'time','time_add','score','hits','hits_day','hits_week','hits_month','up','down','level','rnd'])) {
            $by = 'time';
        }
        if(!in_array($order, ['asc', 'desc'])) {
            $order = 'desc';
        }
        $order= 'art_'.$by .' ' . $order;
        $cach_name = md5('art_listcache_'.http_build_query($where).'_'.$order.'_'.$page.'_'.$num.'_'.$start.'_'.$pageurl);

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
        $key = 'art_detail_'.$where['art_id'][1].$where['art_en'][1];
        $info = Cache::get($key);
        if($GLOBALS['config']['app']['cache_core']==0 || $cache==0 || empty($info)) {
            $info = $this->field($field)->where($where)->find();
            if (empty($info)) {
                return ['code' => 1002, 'msg' => '获取数据失败'];
            }
            $info = $info->toArray();
            //内容
            if (!empty($info['art_content'])) {
                $info['art_page_list'] = mac_art_list($info['art_title'], $info['art_note'], $info['art_content']);
                $info['art_page_total'] = count($info['art_page_list']);
            }
            //分类
            if (!empty($info['type_id'])) {
                $type_list = model('Type')->getCache('type_list');
                $info['type'] = $type_list[$info['type_id']];
                $info['type_1'] = $type_list[$info['type']['type_pid']];
            }

            //用户组
            if (!empty($info['group_id'])) {
                $group_list = model('Group')->getCache('group_list');
                $info['group'] = $group_list[$info['group_id']];
            }
            Cache::set($key,$info);
        }
        return ['code'=>1,'msg'=>'获取成功','info'=>$info];
    }

    public function saveData($data)
    {
        $validate = \think\Loader::validate('Art');
        if(!$validate->check($data)){
            return ['code'=>1001,'msg'=>'参数错误：'.$validate->getError() ];
        }

        $key = 'art_detail_'.$data['art_id'];
        Cache::rm($key);
        $key = 'art_detail_'.$data['art_en'];
        Cache::rm($key);

        $type_list = model('Type')->getCache('type_list');
        $type_info = $type_list[$data['type_id']];
        $data['type_id_1'] = $type_info['type_pid'];

        if(empty($data['art_en'])){
            $data['art_en'] = Pinyin::get($data['art_name']);
        }

        if(empty($data['art_letter'])){
            $data['art_letter'] = strtoupper(substr($data['art_en'],0,1));
        }

        if(!empty($data['art_content'])){
            $data['art_content'] = join('$$$',$data['art_content']);
            $data['art_title'] = join('$$$',$data['art_title']);
            $data['art_note'] = join('$$$',$data['art_note']);
        }
        if(empty($data['art_blurb'])){
            $data['art_blurb'] = mac_substring( str_replace('$$$','', strip_tags($data['art_content'])),100);
        }

        if(!empty($data['art_id'])){
            if($data['uptime']==1){
                $data['art_time'] = time();
            }
            else{
                unset($data['uptime']);
            }
            $where=[];
            $where['art_id'] = ['eq',$data['art_id']];
            $res = $this->allowField(true)->where($where)->update($data);
        }
        else{
            unset($data['uptime']);
            $data['art_time_add'] = time();
            $data['art_time'] = time();
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

        $list = $this->field('art_id,art_name,art_en')->where($where)->select();
        foreach($list as $k=>$v){
            $key = 'art_detail_'.$v['art_id'];
            Cache::rm($key);
            $key = 'art_detail_'.$v['art_en'];
            Cache::rm($key);
        }

        return ['code'=>1,'msg'=>'设置成功'];
    }

    public function updateToday($flag='art')
    {
        $today = strtotime(date('Y-m-d'));
        $where = [];
        $where['art_time'] = ['gt',$today];
        if($flag=='type'){
            $ids = $this->where($where)->column('type_id');
        }
        else{
            $ids = $this->where($where)->column('art_id');
        }
        if(empty($ids)){
            $ids = [];
        }else{
            $ids = array_unique($ids);
        }
        return ['code'=>1,'msg'=>'获取成功','data'=> join(',',$ids) ];
    }

}