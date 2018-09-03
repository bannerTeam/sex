<?php
namespace app\common\model;
use think\Db;
use think\Cache;

class Extend extends Base {


    public function dataCount()
    {
        $key = 'data_count';
        $data = Cache::get($key);
        if(empty($data)){
            $totay = strtotime(date('Y-m-d'));
            $where = [];
            $where['vod_status'] = ['eq',1];
            $tmp = model('Vod')->field('type_id_1,type_id,count(vod_id) as cc')->where($where)->group('type_id_1,type_id')->select();
            foreach($tmp as $k=>$v){
                $data['vod_all'] += intval($v['cc']);
                $list['type_all_'.$v['type_id']] = $v->toArray();
            }

            $where['vod_time'] = ['egt',$totay];
            $tmp = model('Vod')->field('type_id_1,type_id,count(vod_id) as cc')->where($where)->group('type_id_1,type_id')->select();
            foreach($tmp as $k=>$v){
                $data['vod_today'] += intval($v['cc']);
                $list['type_today_'.$v['type_id']] = $v->toArray();
            }


            $where = [];
            $where['art_status'] = ['eq',1];
            $tmp = model('Art')->field('type_id_1,type_id,count(art_id) as cc')->where($where)->group('type_id_1,type_id')->select();
            foreach($tmp as $k=>$v){
                $data['art_all'] += intval($v['cc']);
                $list['type_all_'.$v['type_id']] = $v->toArray();
            }
            $where['art_time'] = ['egt',$totay];
            $tmp = model('Art')->field('type_id_1,type_id,count(art_id) as cc')->where($where)->group('type_id_1,type_id')->select();
            foreach($tmp as $k=>$v){
                $data['art_today'] += intval($v['cc']);
                $list['type_today_'.$v['type_id']] = $v->toArray();
            }

            foreach($list as $k=>$v) {
                $data[$k]=$v['cc'];

                if(strpos($k,'type_all')!==false){
                    $data['type_all_' . $v['type_id_1']] += $v['cc'];
                }
                if(strpos($k,'type_today')!==false){
                    $data['type_today_' . $v['type_id_1']] += $v['cc'];
                }
            }
            Cache::set($key,$data,$GLOBALS['config']['app']['cache_time']);
        }
        return $data;
    }

    public function areaData($lp)
    {
        $order = $lp['order'];
        $start = intval(abs($lp['start']));
        $num = intval(abs($lp['num']));
        $tid = intval($lp['tid']);

        $config = config('maccms.app');
        $data_str = $config['vod_extend_area'];
        if($tid>0){
            $type_list = model('Type')->getCache('tree_list');
            $type_info = $type_list[$tid];
            if(!empty($type_info)){
                $type_extend = json_decode($type_info['type_extend'],true);
                $data_str = $type_extend['area'];
            }
        }

        if(empty($num)){
            $num = 20;
        }
        if($start>1){
            $start--;
        }

        $tmp = explode(',',$data_str);
        if($order=='desc'){
            $tmp = array_reverse($tmp);
        }
        $list = [];
        foreach($tmp as $k=>$v){
            if($k>=$start && $k<$num){
                $list[] = ['area_name' => $v];
            }
        }

        $list = array_filter($list);
        $total = count($list);

        $cach_name = md5('area_listcache_'.join('&',$lp).'_'.$order.'_'.$num.'_'.$start);

        return ['code'=>1,'msg'=>'数据列表','page'=>1,'limit'=>$num,'total'=>$total,'list'=>$list];
    }

    public function langData($lp)
    {
        $order = $lp['order'];
        $start = intval(abs($lp['start']));
        $num = intval(abs($lp['num']));
        $tid = intval($lp['tid']);

        $config = config('maccms.app');
        $data_str = $config['vod_extend_lang'];
        if($tid>0){
            $type_list = model('Type')->getCache('tree_list');
            $type_info = $type_list[$tid];
            if(!empty($type_info)){
                $type_extend = json_decode($type_info['type_extend'],true);
                $data_str = $type_extend['lang'];
            }
        }

        if(empty($num)){
            $num = 20;
        }
        if($start>1){
            $start--;
        }

        $tmp = explode(',',$data_str);
        if($order=='desc'){
            $tmp = array_reverse($tmp);
        }
        $list = [];
        foreach($tmp as $k=>$v){
            if($k>=$start && $k<$num){
                $list[] = ['lang_name' => $v];
            }
        }
        $list = array_filter($list);
        $total = count($list);

        $cach_name = md5('lang_listcache_'.join('&',$lp).'_'.$order.'_'.$num.'_'.$start);

        return ['code'=>1,'msg'=>'数据列表','page'=>1,'limit'=>$num,'total'=>$total,'list'=>$list];
    }

    public function classData($lp)
    {
        $order = $lp['order'];
        $start = intval(abs($lp['start']));
        $num = intval(abs($lp['num']));
        $tid = intval($lp['tid']);

        $config = config('maccms.app');
        $data_str = $config['vod_extend_class'];
        if($tid>0){
            $type_list = model('Type')->getCache('tree_list');
            $type_info = $type_list[$tid];
            if(!empty($type_info)){
                $type_extend = json_decode($type_info['type_extend'],true);
                $data_str = $type_extend['class'];
            }
        }

        if(empty($num)){
            $num = 20;
        }
        if($start>1){
            $start--;
        }

        $tmp = explode(',',$data_str);
        if($order=='desc'){
            $tmp = array_reverse($tmp);
        }
        $list = [];
        foreach($tmp as $k=>$v){
            if($k>=$start && $k<$num){
                $list[] = ['class_name' => $v];
            }
        }
        $list = array_filter($list);
        $total = count($list);

        $cach_name = md5('class_listcache_'.join('&',$lp).'_'.$order.'_'.$num.'_'.$start);

        return ['code'=>1,'msg'=>'数据列表','page'=>1,'limit'=>$num,'total'=>$total,'list'=>$list];
    }

    public function yearData($lp)
    {
        $order = $lp['order'];
        $start = intval(abs($lp['start']));
        $num = intval(abs($lp['num']));
        $tid = intval($lp['tid']);

        $config = config('maccms.app');
        $data_str = $config['vod_extend_year'];
        if($tid>0){
            $type_list = model('Type')->getCache('tree_list');
            $type_info = $type_list[$tid];
            if(!empty($type_info)){
                $type_extend = json_decode($type_info['type_extend'],true);
                $data_str = $type_extend['year'];
            }
        }

        if(empty($num)){
            $num = 20;
        }
        if($start>1){
            $start--;
        }

        $tmp = explode(',',$data_str);
        if($order=='desc'){
            $tmp = array_reverse($tmp);
        }
        $list = [];
        foreach($tmp as $k=>$v){
            if($k>=$start && $k<$num){
                $list[] = ['year_name' => $v];
            }
        }
        $list = array_filter($list);
        $total = count($list);

        $cach_name = md5('year_listcache_'.join('&',$lp).'_'.$order.'_'.$num.'_'.$start);

        return ['code'=>1,'msg'=>'数据列表','page'=>1,'limit'=>$num,'total'=>$total,'list'=>$list];
    }

    public function versionData($lp)
    {
        $order = $lp['order'];
        $start = intval(abs($lp['start']));
        $num = intval(abs($lp['num']));
        $tid = intval($lp['tid']);

        $config = config('maccms.app');
        $data_str = $config['vod_extend_version'];
        if($tid>0){
            $type_list = model('Type')->getCache('tree_list');
            $type_info = $type_list[$tid];
            if(!empty($type_info)){
                $type_extend = json_decode($type_info['type_extend'],true);
                $data_str = $type_extend['version'];
            }
        }

        if(empty($num)){
            $num = 20;
        }
        if($start>1){
            $start--;
        }

        $tmp = explode(',',$data_str);
        if($order=='desc'){
            $tmp = array_reverse($tmp);
        }
        $list = [];
        foreach($tmp as $k=>$v){
            if($k>=$start && $k<$num){
                $list[] = ['version_name' => $v];
            }
        }

        $list = array_filter($list);
        $total = count($list);

        $cach_name = md5('version_listcache_'.join('&',$lp).'_'.$order.'_'.$num.'_'.$start);

        return ['code'=>1,'msg'=>'数据列表','page'=>1,'limit'=>$num,'total'=>$total,'list'=>$list];
    }

    public function stateData($lp)
    {
        $order = $lp['order'];
        $start = intval(abs($lp['start']));
        $num = intval(abs($lp['num']));
        $tid = intval($lp['tid']);

        $config = config('maccms.app');
        $data_str = $config['vod_extend_state'];
        if($tid>0){
            $type_list = model('Type')->getCache('tree_list');
            $type_info = $type_list[$tid];
            if(!empty($type_info)){
                $type_extend = json_decode($type_info['type_extend'],true);
                $data_str = $type_extend['state'];
            }
        }

        if (!in_array($order, ['asc', 'desc'])) {
            $order = 'asc';
        }

        if(empty($num)){
            $num = 20;
        }
        if($start>1){
            $start--;
        }

        $tmp = explode(',',$data_str);
        if($order=='desc'){
            $tmp = array_reverse($tmp);
        }
        $list = [];
        foreach($tmp as $k=>$v){
            if($k>=$start && $k<$num){
                $list[] = ['state_name' => $v];
            }
        }
        $list = array_filter($list);
        $total = count($list);

        $cach_name = md5('state_listcache_'.join('&',$lp).'_'.$order.'_'.$num.'_'.$start);

        return ['code'=>1,'msg'=>'数据列表','page'=>1,'limit'=>$num,'total'=>$total,'list'=>$list];
    }

    public function letterData($lp)
    {
        $data_str = 'A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,0-9';
        $tmp = explode(',',$data_str);

        $order = $lp['order'];
        $start = intval(abs($lp['start']));
        $num = intval(abs($lp['num']));
        $tid = intval($lp['tid']);

        if (!in_array($order, ['asc', 'desc'])) {
            $order = 'asc';
        }

        if(empty($num)){
            $num = 20;
        }
        if($start>1){
            $start--;
        }

        if($tid>0){

        }

        if($order=='desc'){
            $tmp = array_reverse($tmp);
        }
        $list = [];
        foreach($tmp as $k=>$v){
            if($k>=$start && $k<$num){
                $list[] = ['letter_name' => $v];
            }
        }

        $list = array_filter($list);
        $total = count($list);

        $cach_name = md5('letter_listcache_'.join('&',$lp).'_'.$order.'_'.$num.'_'.$start);

        return ['code'=>1,'msg'=>'数据列表','page'=>1,'limit'=>$num,'total'=>$total,'list'=>$list];
    }



}