<?php
namespace app\common\model;
use think\Db;
use think\Cache;
use app\common\util\Pinyin;

class Vod extends Base {
    // 设置数据表（不含前缀）
    protected $name = 'vod';

    // 定义时间戳字段名
    protected $createTime = '';
    protected $updateTime = '';

    // 自动完成
    protected $auto       = [];
    protected $insert     = [];
    protected $update     = [];

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
        
        
        $list = Db::name('Vod')->field($field)->where($where)->order($order)->limit($limit_str)->select();

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
            ->join('tmpvod t','t.name1 = vod_name')
            ->where($where)
            ->count();

        $list = Db::name('Vod')
            ->join('tmpvod t','t.name1 = vod_name')
            ->field($field)
            ->where($where)
            ->order($order)
            ->limit($limit_str)
            ->select();

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
        if(!is_array($lp)){
            $lp = json_decode($lp,true);
        }

        $order = $lp['order'];
        $by = $lp['by'];
        $type = $lp['type'];
        $ids = $lp['ids'];
        $paging = $lp['paging'];
        $pageurl = $lp['pageurl'];
        $level = $lp['level'];
        $area = $lp['area'];
        $lang = $lp['lang'];
        $state = $lp['state'];
        $wd = $lp['wd'];
        $tag = $lp['tag'];
        $class = $lp['class'];
        $letter = $lp['letter'];
        $actor = $lp['actor'];
        $director = $lp['director'];
        $version = $lp['version'];
        $year = intval($lp['year']);
        $start = intval(abs($lp['start']));
        $num = intval(abs($lp['num']));
        $half = intval(abs($lp['half']));
        
        $timeadd =  intval($lp['timeadd']);
        
        $page = 1;
        $where=[];
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

        $param = mac_param_url();

        if($paging=='yes') {
            $totalshow = 1;
            if(!empty($param['id'])){
                $type = intval($param['id']);
            }
            if(!empty($param['level'])){
                $level = intval($param['level']);
            }
            if(!empty($param['year'])){
                if(strlen($param['year'])==4){
                    $year = intval($param['year']);
                }
                elseif(strlen($param['year'])==9){
                    $tmp = explode('-',$param['year']);
                    $s1 = intval($tmp[0]);$s2 = intval($tmp[1]);
                    if($s1>$s2){
                        $s1 = intval($tmp[1]);$s2 = intval($tmp[0]);
                    }

                    $tmp=[];
                    for($i=$s1;$i<=$s2;$i++){
                        $tmp[] = $i;
                    }
                    $year = join(',',$tmp);
                }
            }
            if(!empty($param['area'])){
                $area = $param['area'];
            }
            if(!empty($param['lang'])){
                $lang = $param['lang'];
            }
            if(!empty($param['tag'])){
                $tag = $param['tag'];
            }
            if(!empty($param['class'])){
                $class = $param['class'];
            }
            if(!empty($param['state'])){
                $state = $param['state'];
            }
            if(!empty($param['letter'])){
                $letter = $param['letter'];
            }
            if(!empty($param['version'])){
                $version = $param['version'];
            }
            if(!empty($param['actor'])){
                $actor = $param['actor'];
            }
            if(!empty($param['director'])){
                $director = $param['director'];
            }
            if(!empty($param['wd'])){
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
            if(!in_array($pageurl,['vod/type','vod/show','vod/search','index/index'])){
                $pageurl = 'vod/type';
            }
            $param['page'] = 'PAGELINK';

            if($pageurl=='vod/type'){
                $type = intval( MAC_TYPE_ID );
                $type_list = model('Type')->getCache('type_list');
                $type_info = $type_list[$type];
                $pageurl = mac_url_type($type_info,$param);
            }
            else{
                $pageurl = url($pageurl,$param);
            }
        }

        $where['vod_status'] = ['eq',1];
        if(!empty($ids)) {
            if($ids!='all'){
                $where['vod_id'] = ['in',$ids];
            }
        }
        if(!empty($level)) {
            $where['vod_level'] = ['in',explode(',',$level)];
        }
        if(!empty($year)) {
            $where['vod_year'] = ['in',explode(',',$year)];
        }
        if(!empty($area)) {
            $where['vod_area'] = ['in',explode(',',$area)];
        }
        if(!empty($lang)) {
            $where['vod_lang'] = ['in',explode(',',$lang)];
        }
        if(!empty($state)) {
            $where['vod_state'] = ['eq',$state];
        }
        if(!empty($version)) {
            $where['vod_version'] = ['eq',$version];
        }
        if(!empty($letter)){
            if($letter=='0-9'){
                $letter='0,1,2,3,4,5,6,7,8,9';
            }
            $where['vod_letter'] = ['in',explode(',',$letter)];
        }
        if(!empty($type)) {
            if($type=='current'){
                $type = intval( MAC_TYPE_ID );
                $where['vod_id'] = ['not in',$param['id']];
            }
            if($type!='all') {
                $tmp_arr = explode(',',$type);
                $type_list = model('Type')->getCache('type_list');
                $type = [];
                foreach($type_list as $k2=>$v2){
                    if(in_array($v2['type_id'].'',$tmp_arr) || in_array($v2['type_pid'].'',$tmp_arr)){
                        $type[]=$v2['type_id'];
                    }
                }
                $type = array_unique($type);
                $where['type_id'] = ['in', implode(',',$type) ];
            }

        }

        if(!empty($wd)) {
            $where['vod_name|vod_en|vod_sub'] = ['like', '%' . $wd . '%'];
        }
        if(!empty($tag)) {
            $where['vod_tag'] = ['like', '%' . $tag . '%'];
        }
        if(!empty($class)) {
            $where['vod_class'] = ['like', '%' . $class . '%'];
        }
        if(!empty($actor)) {
            $where['vod_actor'] = ['like', '%' . $actor . '%'];
        }
        if(!empty($director)) {
            $where['vod_director'] = ['like', '%' . $director . '%'];
        }

        if(!in_array($by, ['id', 'time','time_add','score','hits','hits_day','hits_week','hits_month','up','down','level','rnd'])) {
            $by = 'time';
        }
        
        
        /**
         * 时间范围检索  单位 天
         */
        if(!empty($timeadd)){            
            $start_time = strtotime('-'.$timeadd.' day');            
            $where['vod_time_add'] = ['gt', $start_time];
            
        }        
        
        if(!in_array($order, ['asc', 'desc'])) {
            $order = 'desc';
        }
        $order= 'vod_'.$by .' ' . $order;
        $cach_name = md5('vod_listcache_'.http_build_query($where).'_'.$order.'_'.$page.'_'.$num.'_'.$start.'_'.$pageurl);

        $res = Cache::get($cach_name);
        if($GLOBALS['config']['app']['cache_core']==0 || empty($res)) {
            $res = $this->listData($where, $order, $page, $num, $start,'*',1, $totalshow);
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

        $key = 'vod_detail_'.$where['vod_id'][1].$where['vod_en'][1];

        $info = Cache::get($key);
        if($GLOBALS['config']['app']['cache_core']==0 || $cache==0 || empty($info)) {
            $info = $this->field($field)->where($where)->find();

            if (empty($info)) {
                return ['code' => 1002, 'msg' => '获取数据失败'];
            }
            $info = $info->toArray();

            if (!empty($info['vod_play_from'])) {
                $info['vod_play_list'] = mac_play_list($info['vod_play_from'], $info['vod_play_url'], $info['vod_play_server'], $info['vod_play_note'], 'play');
            }
            if (!empty($info['vod_down_from'])) {
                $info['vod_down_list'] = mac_play_list($info['vod_down_from'], $info['vod_down_url'], $info['vod_down_server'], $info['vod_down_note'], 'down');
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
        $validate = \think\Loader::validate('Vod');
        if(!$validate->check($data)){
            return ['code'=>1001,'msg'=>'参数错误：'.$validate->getError() ];
        }
        $key = 'vod_detail_'.$data['vod_id'];
        Cache::rm($key);
        $key = 'vod_detail_'.$data['vod_en'];
        Cache::rm($key);

        $type_list = model('Type')->getCache('type_list');
        $type_info = $type_list[$data['type_id']];
        $data['type_id_1'] = $type_info['type_pid'];

        if(empty($data['vod_en'])){
            $data['vod_en'] = Pinyin::get($data['vod_name']);
        }

        if(empty($data['vod_letter'])){
            $data['vod_letter'] = strtoupper(substr($data['vod_en'],0,1));
        }

        if(empty($data['vod_blurb'])){
            $data['vod_blurb'] = mac_substring( strip_tags($data['vod_content']) ,100);
        }

        if(empty($data['vod_play_url'])){
            $data['vod_play_url'] = '';
        }
        if(empty($data['vod_down_url'])){
            $data['vod_down_url'] = '';
        }

        if(!empty($data['vod_play_from'])) {
            $data['vod_play_from'] = join('$$$', $data['vod_play_from']);
            $data['vod_play_server'] = join('$$$', $data['vod_play_server']);
            $data['vod_play_note'] = join('$$$', $data['vod_play_note']);
            $data['vod_play_url'] = join('$$$', $data['vod_play_url']);
            $data['vod_play_url'] = str_replace( array(chr(10),chr(13)), array('','#'),$data['vod_play_url']);
        }
        else{
            $data['vod_play_from'] = '';
            $data['vod_play_server'] = '';
            $data['vod_play_note'] = '';
            $data['vod_play_url'] = '';
        }

        if(!empty($data['vod_down_from'])) {
            $data['vod_down_from'] = join('$$$', $data['vod_down_from']);
            $data['vod_down_server'] = join('$$$', $data['vod_down_server']);
            $data['vod_down_note'] = join('$$$', $data['vod_down_note']);
            $data['vod_down_url'] = join('$$$', $data['vod_down_url']);
            $data['vod_down_url'] = str_replace(array(chr(10),chr(13)), array('','#'),$data['vod_down_url']);
        }else{
            $data['vod_down_from']='';
            $data['vod_down_server']='';
            $data['vod_down_note']='';
            $data['vod_down_url']='';
        }

        if(!empty($data['vod_id'])){
            if($data['uptime']==1){
                $data['vod_time'] = time();
            }
            else{
                unset($data['uptime']);
            }

            $where=[];
            $where['vod_id'] = ['eq',$data['vod_id']];
            $res = $this->allowField(true)->where($where)->update($data);
        }
        else{
            unset($data['uptime']);
            $data['vod_time_add'] = time();
            $data['vod_time'] = time();
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

        $list = $this->field('vod_id,vod_name,vod_en')->where($where)->select();
        foreach($list as $k=>$v){
            $key = 'vod_detail_'.$v['vod_id'];
            Cache::rm($key);
            $key = 'vod_detail_'.$v['vod_en'];
            Cache::rm($key);
        }

        return ['code'=>1,'msg'=>'设置成功'];
    }

    public function updateToday($flag='vod')
    {
        $today = strtotime(date('Y-m-d'));
        $where = [];
        $where['vod_time'] = ['gt',$today];
        if($flag=='type'){
            $ids = $this->where($where)->column('type_id');
        }
        else{
            $ids = $this->where($where)->column('vod_id');
        }
        if(empty($ids)){
            $ids = [];
        }else{
            $ids = array_unique($ids);
        }
        return ['code'=>1,'msg'=>'获取成功','data'=> join(',',$ids) ];
    }

}