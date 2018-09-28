<?php
namespace app\common\model;
use think\Db;
use think\Cache;
use app\common\util\Pinyin;

class Collect extends Base {

    // 设置数据表（不含前缀）
    protected $name = 'collect';

    // 定义时间戳字段名
    protected $createTime = '';
    protected $updateTime = '';

    // 自动完成
    protected $auto       = [];
    protected $insert     = [];
    protected $update     = [];

    public function listData($where,$order,$page,$limit=20)
    {
    	$total = $this->where($where)->count();
        $list = Db::name('Collect')->where($where)->order($order)->page($page)->limit($limit)->select();
        return ['code'=>1,'msg'=>'数据列表','page'=>$page,'pagecount'=>ceil($total/$limit),'limit'=>$limit,'total'=>$total,'list'=>$list];
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
        $validate = \think\Loader::validate('Collect');
        if(!empty($data['collect_id'])){
            if(!$validate->scene('edit')->check($data)){
                return ['code'=>1001,'msg'=>'参数错误：'.$validate->getError() ];
            }

            $where=[];
            $where['collect_id'] = ['eq',$data['collect_id']];
            $res = $this->where($where)->update($data);
        }
        else{
            if(!$validate->scene('edit')->check($data)){
                return ['code'=>1002,'msg'=>'参数错误：'.$validate->getError() ];
            }
            $res = $this->insert($data);
        }
        if(false === $res){
            return ['code'=>1003,'msg'=>''.$this->getError() ];
        }
        return ['code'=>1,'msg'=>'保存成功'];
    }

    public function delData($where)
    {
        $res = $this->where($where)->delete();
        if($res===false){
            return ['code'=>1001,'msg'=>'删除失败'.$this->getError() ];
        }
        return ['code'=>1,'msg'=>'删除成功'];
    }


    public function vod($param)
    {
        
       
        if($param['type'] == '1'){
            return $this->vod_xml($param);
        }
        elseif($param['type'] == '2'){
            return $this->vod_json($param);
        }
        else{
            $data = $this->vod_json($param);  
            if($data['code'] == 1){
                return $data;
            }
            else{
                return $this->vod_xml($param);
            }
        }
    }

    public function art($param)
    {
        return $this->art_json($param);
    }

    public function vod_xml_replace($url)
    {
        $array_url = array();
        $arr_ji = explode('#',str_replace('||','//',$url));
        foreach($arr_ji as $key=>$value){
            $urlji = explode('$',$value);
            if( count($urlji) > 1 ){
                $array_url[$key] = $urlji[0].'$'.trim($urlji[1]);
            }else{
                $array_url[$key] = trim($urlji[0]);
            }
        }
        return implode('#',$array_url);
    }

    public function vod_xml($param,$html='')
    {
        $url_param = [];
        $url_param['ac'] = $param['ac'];
        $url_param['t'] = $param['t'];
        $url_param['pg'] = is_numeric($param['page']) ? $param['page'] : '';
        $url_param['h'] = $param['h'];
        $url_param['ids'] = $param['ids'];
        $url_param['wd'] = $param['wd'];
        if(empty($param['h']) && !empty($param['rday'])){
            $url_param['h'] = $param['rday'];
        }

        if($param['ac']!='list'){
            $url_param['ac'] = 'videolist';
        }

        $url = $param['cjurl'];
        if(strpos($url,'?')===false){
            $url .='?';
        }
        else{
            $url .='&';
        }
        $url .= http_build_query($url_param). base64_decode($param['param']);
        $html = mac_curl_get($url);
        
        if(empty($html)){
            return ['code'=>1001, 'msg'=>'连接API资源库失败，通常为服务器网络不稳定或禁用了采集'];
        }
        
        $xml = @simplexml_load_string($html);        
        
        if(empty($xml)){
            $labelRule = '<pic>'."(.*?)".'</pic>';
            $labelRule = mac_buildregx($labelRule,"is");
            preg_match_all($labelRule,$html,$tmparr);
            $ec=false;
            foreach($tmparr[1] as $tt){
                if(strpos($tt,'&')!==false){
                    $ec=true;
                    $ne = str_replace(['&'],['&amp;'],$tt);
                    $html = str_replace($tt,$ne,$html);
                }
            }
            if($ec) {
                $xml = @simplexml_load_string($html);
            }
            if(empty($xml)) {
                return ['code' => 1002, 'msg' => 'XML格式不正确，不支持采集'];
            }
        }

        $array_page = [];
        $array_page['page'] = (string)$xml->list->attributes()->page;
        $array_page['pagecount'] = (string)$xml->list->attributes()->pagecount;
        $array_page['pagesize'] = (string)$xml->list->attributes()->pagesize;
        $array_page['recordcount'] = (string)$xml->list->attributes()->recordcount;
        $array_page['url'] = $url;

        $type_list = model('Type')->getCache('type_list');
        $bind_list = config('bind');


        $key = 0;
        $array_data = [];
        foreach($xml->list->video as $video){
            $bind_key = $param['cjflag'] .'_'.(string)$video->tid;
            if($bind_list[$bind_key] >0){
                $array_data[$key]['type_id'] = $bind_list[$bind_key];
            }
            else{
                $array_data[$key]['type_id'] = 0;
            }
            $array_data[$key]['vod_id'] = (string)$video->id;
            //$array_data[$key]['type_id'] = (string)$video->tid;
            $array_data[$key]['vod_name'] = (string)$video->name;
            $array_data[$key]['vod_remarks'] = (string)$video->note;
            $array_data[$key]['type_name'] = (string)$video->type;
            $array_data[$key]['vod_pic'] = (string)$video->pic;
            $array_data[$key]['vod_lang'] = (string)$video->lang;
            $array_data[$key]['vod_area'] = (string)$video->area;
            $array_data[$key]['vod_year'] = (string)$video->year;
            $array_data[$key]['vod_serial'] = (string)$video->state;
            $array_data[$key]['vod_actor'] = (string)$video->actor;
            $array_data[$key]['vod_director'] = (string)$video->director;
            $array_data[$key]['vod_content'] = (string)$video->des;

            $array_data[$key]['vod_status'] = 1;
            $array_data[$key]['vod_type'] = $array_data[$key]['list_name'];
            $array_data[$key]['vod_time'] = (string)$video->last;
            $array_data[$key]['vod_total'] = 0;
            $array_data[$key]['vod_isend'] = 1;
            if($array_data[$key]['vod_serial']){
                $array_data[$key]['vod_isend'] = 0;
            }
            //格式化地址与播放器
            $array_from = [];
            $array_url = [];
            $array_server=[];
            $array_note=[];
            //videolist|list播放列表不同
            if($count=count($video->dl->dd)){
                for($i=0; $i<$count; $i++){
                    $array_from[$i] = (string)$video->dl->dd[$i]['flag'];
                    $array_url[$i] = $this->vod_xml_replace((string)$video->dl->dd[$i]);
                    $array_server[$i] = 'no';
                    $array_note[$i] = '';

                }
            }else{
                $array_from[]=(string)$video->dt;
                $array_url[] ='';
                $array_server[]='';
                $array_note[]='';
            }

            if(strpos(base64_decode($param['param']),'ct=1')!==false){
                $array_data[$key]['vod_down_from'] = implode('$$$', $array_from);
                $array_data[$key]['vod_down_url'] = implode('$$$', $array_url);
                $array_data[$key]['vod_down_server'] = implode('$$$', $array_server);
                $array_data[$key]['vod_down_note'] = implode('$$$', $array_note);
            }
            else{
                $array_data[$key]['vod_play_from'] = implode('$$$', $array_from);
                $array_data[$key]['vod_play_url'] = implode('$$$', $array_url);
                $array_data[$key]['vod_play_server'] = implode('$$$', $array_server);
                $array_data[$key]['vod_play_note'] = implode('$$$', $array_note);
            }

            $key++;
        }

        $array_type = [];
        $key=0;
        //分类列表
        if($param['ac'] == 'list'){
            foreach($xml->class->ty as $ty){
                $array_type[$key]['type_id'] = (string)$ty->attributes()->id;
                $array_type[$key]['type_name'] = (string)$ty;
                $key++;
            }
        }

        $res = ['code'=>1, 'msg'=>'xml', 'page'=>$array_page, 'type'=>$array_type, 'data'=>$array_data ];
        return $res;
    }

    public function vod_json($param)
    {
        $url_param = [];
        $url_param['ac'] = $param['ac'];
        $url_param['t'] = $param['t'];
        $url_param['pg'] = is_numeric($param['page']) ? $param['page'] : '';
        $url_param['h'] = $param['h'];
        $url_param['ids'] = $param['ids'];
        $url_param['wd'] = $param['wd'];

        if($param['ac']!='list'){
            $url_param['ac'] = 'videolist';
        }

        $url = $param['cjurl'];
        if(strpos($url,'?')===false){
            $url .='?';
        }
        else{
            $url .='&';
        }
        $url .= http_build_query($url_param). base64_decode($param['param']);
        $html = mac_curl_get($url);
        if(empty($html)){
            return ['code'=>1001, 'msg'=>'连接API资源库失败，通常为服务器网络不稳定或禁用了采集'];
        }
       
        $json = json_decode($html,true);
        if(!$json){
            return ['code'=>1002, 'msg'=>'JSON格式不正确，不支持采集'];
        }
        
        print_r($json);
        exit;
        $array_page = [];
        $array_page['page'] = $json['page'];
        $array_page['pagecount'] = $json['pagecount'];
        $array_page['pagesize'] = $json['limit'];
        $array_page['recordcount'] = $json['total'];
        $array_page['url'] = $url;

        $type_list = model('Type')->getCache('type_list');
        $bind_list = config('bind');

        $key = 0;
        $array_data = [];
        foreach($json['list'] as $key=>$v){
            $array_data[$key] = $v;
            $bind_key = $param['cjflag'] .'_'.$v['type_id'];
            if($bind_list[$bind_key] >0){
                $array_data[$key]['type_id'] = $bind_list[$bind_key];
            }
            else{
                $array_data[$key]['type_id'] = 0;
            }

            if(!empty($v['dl'])) {
                //格式化地址与播放器
                $array_from = [];
                $array_url = [];
                $array_server = [];
                $array_note = [];
                //videolist|list播放列表不同
                foreach ($v['dl'] as $k2 => $v2) {
                    $array_from[] = $k2;
                    $array_url[] = $v2;
                    $array_server[] = 'no';
                    $array_note[] = '';
                }

                $array_data[$key]['vod_play_from'] = implode('$$$', $array_from);
                $array_data[$key]['vod_play_url'] = implode('$$$', $array_url);
                $array_data[$key]['vod_play_server'] = implode('$$$', $array_server);
                $array_data[$key]['vod_play_note'] = implode('$$$', $array_note);
            }
        }

        $array_type = [];
        $key=0;
        //分类列表
        if($param['ac'] == 'list'){
            foreach($json['class'] as $k=>$v){
                $array_type[$key]['type_id'] = $v['type_id'];
                $array_type[$key]['type_name'] = $v['type_name'];
                $key++;
            }
        }

        $res = ['code'=>1, 'msg'=>'json', 'page'=>$array_page, 'type'=>$array_type, 'data'=>$array_data ];
        return $res;
    }

    public function syncImages($pic_status,$pic_url,$flag='vod')
    {
        if($pic_status == 1){
            $img_url = model('Image')->down_load($pic_url, $GLOBALS['config']['upload'], $flag);
            $link = MAC_PATH . $img_url;
            if ($GLOBALS['config']['upload']['mode'] > 1) {
                $link = str_replace('mac:', 'http:', $img_url);
            }
            if ($img_url == $pic_url) {
                $des = '<a href="' . $link . '" target="_blank">' . $link . '</a><font color=red>下载失败!</font>';
            } else {
                $pic_url = $img_url;
                $des = '<a href="' . $link . '" target="_blank">' . $link . '</a><font color=green>下载成功!</font>';
            }
        }
        return ['pic'=>$pic_url,'msg'=>$des];
    }

    public function vod_data($param,$data,$show=1)
    {
        if($show==1) {
            mac_echo('当前采集任务<strong class="green">' . $data['page']['page'] . '</strong>/<span class="green">' . $data['page']['pagecount'] . '</span>页 采集地址&nbsp;' . $data['page']['url'] . '');
        }

        $config = config('maccms.collect');
        $config = $config['vod'];

        $type_list = model('Type')->getCache('type_list');
        
        foreach($data['data'] as $k=>$v){
            $color='red';
            $des='';
            if($v['type_id'] ==0){
                $des = '分类未绑定，跳过err';
            }
            elseif(empty($v['vod_name'])) {
                $des = '数据不完整，跳过err';
            }
            elseif(strpos(','.$config['filter'],$v['vod_name'])) {
                $des = '数据在过滤单中，跳过err';
            }
            else {
                unset($v['vod_id']);

                $v['type_id_1'] = intval($type_list[$v['type_id']]['type_pid']);
                $v['vod_en'] = Pinyin::get($v['vod_name']);
                $v['vod_letter'] = strtoupper(substr($v['vod_en'],0,1));
                $v['vod_time_add'] = time();
                $v['vod_time'] = time();
                $v['vod_status'] = $config['status'];

                $v['vod_lock'] = intval($v['vod_lock']);
                $v['vod_status'] = intval($v['vod_status']);

                $v['vod_year'] = intval($v['vod_year']);
                $v['vod_level'] = intval($v['vod_level']);
                $v['vod_hits'] = intval($v['vod_hits']);
                $v['vod_hits_day'] = intval($v['vod_hits_day']);
                $v['vod_hits_week'] = intval($v['vod_hits_week']);
                $v['vod_hits_month'] = intval($v['vod_hits_month']);
                $v['vod_stint_play'] = intval($v['vod_stint_play']);
                $v['vod_stint_down'] = intval($v['vod_stint_down']);

                $v['vod_total'] = intval($v['vod_total']);
                $v['vod_serial'] = intval($v['vod_serial']);
                $v['vod_isend'] = intval($v['vod_isend']);
                $v['vod_up'] = intval($v['vod_up']);
                $v['vod_down'] = intval($v['vod_down']);

                $v['vod_score'] = floatval($v['vod_score']);
                $v['vod_score_all'] = intval($v['vod_score_all']);
                $v['vod_score_num'] = intval($v['vod_score_num']);

                $v['vod_class'] = mac_txt_merge($v['vod_class'],$v['type_name']);

                $v['vod_actor'] = mac_format_text($v['vod_actor']);
                $v['vod_director'] = mac_format_text($v['vod_director']);
                $v['vod_class'] = mac_format_text($v['vod_class']);
                $v['vod_tag'] = mac_format_text($v['vod_tag']);

                if(empty($v['vod_isend']) && !empty($v['vod_serial'])){
                    $v['vod_isend'] = 0;
                }

                if($config['hits_start']>0 && $config['hits_end']>0) {
                    $v['vod_hits'] = rand($config['hits_start'], $config['hits_end']);
                    $v['vod_hits_day'] = rand($config['hits_start'], $config['hits_end']);
                    $v['vod_hits_week'] = rand($config['hits_start'], $config['hits_end']);
                    $v['vod_hits_month'] = rand($config['hits_start'], $config['hits_end']);
                }

                if($config['updown_start']>0 && $config['updown_end']){
                    $v['vod_up'] = rand($config['updown_start'], $config['updown_end']);
                    $v['vod_down'] = rand($config['updown_start'], $config['updown_end']);
                }

                if($config['score']==1) {
                    $v['vod_score_num'] = rand(1, 1000);
                    $v['vod_score_all'] = $v['vod_score_num'] * rand(1, 10);
                    $v['vod_score'] = round($v['vod_score_all'] / $v['vod_score_num'], 1);
                }

                if ($config['psernd'] == 1) {
                    $v['vod_content'] = mac_rep_pse_rnd($config['words'], $v['vod_content']);
                }
                if ($config['psesyn'] == 1) {
                    $v['vod_content'] = mac_rep_pse_syn($config['thesaurus'], $v['vod_content']);
                }

                if(empty($v['vod_blurb'])){
                    $v['vod_blurb'] = mac_substring( strip_tags($v['vod_content']) ,100);
                }

                $where = [];
                $where['vod_name'] = $v['vod_name'];

                if (strpos($config['inrule'], 'b')) {
                    $where['type_id'] = $v['type_id'];
                }
                if (strpos($config['inrule'], 'c')) {
                    $where['vod_year'] = $v['vod_year'];
                }
                if (strpos($config['inrule'], 'd')) {
                    $where['vod_area'] = $v['vod_area'];
                }
                if (strpos($config['inrule'], 'e')) {
                    $where['vod_lang'] = $v['vod_lang'];
                }
                if (strpos($config['inrule'], 'f')) {
                    $where['vod_actor'] = $v['vod_actor'];
                }
                if (strpos($config['inrule'], 'g')) {
                    $where['vod_director'] = $v['vod_director'];
                }
                if ($config['tag'] == 1) {
                    $v['vod_tag'] = mac_get_tag($v['vod_name'], $v['vod_content']);
                }

                if(empty($v['vod_play_url'])){
                    $v['vod_play_url'] = '';
                }
                if(empty($v['vod_down_url'])){
                    $v['vod_down_url'] = '';
                }
                //验证地址
                $cj_play_from_arr = explode('$$$',$v['vod_play_from'] );
                $cj_play_url_arr = explode('$$$',$v['vod_play_url']);
                $cj_play_server_arr = explode('$$$',$v['vod_play_server']);
                $cj_play_note_arr = explode('$$$',$v['vod_play_note']);
                $cj_down_from_arr = explode('$$$',$v['vod_down_from'] );
                $cj_down_url_arr = explode('$$$',$v['vod_down_url']);
                $cj_down_server_arr = explode('$$$',$v['vod_down_server']);
                $cj_down_note_arr = explode('$$$',$v['vod_down_note']);
                foreach($cj_play_from_arr as $kk=>$vv){
                    if(empty($vv)){
                        unset($cj_play_from_arr[$kk]);
                        continue;
                    }
                    $cj_play_url_arr[$kk] = rtrim($cj_play_url_arr[$kk],'#');
                    $cj_play_server_arr[$kk] = $cj_play_server_arr[$kk];
                    $cj_play_note_arr[$kk] = $cj_play_note_arr[$kk];
                }
                foreach($cj_down_from_arr as $kk=>$vv){
                    if(empty($vv)){
                        unset($cj_down_from_arr[$kk]);
                        continue;
                    }
                    $cj_down_url_arr[$kk] = rtrim($cj_down_url_arr[$kk]);
                    $cj_down_server_arr[$kk] = $cj_down_server_arr[$kk];
                    $cj_down_note_arr[$kk] = $cj_down_note_arr[$kk];
                }
                $v['vod_play_from'] = join('$$$',$cj_play_from_arr);
                $v['vod_play_url'] = join('$$$',$cj_play_url_arr);
                $v['vod_play_server'] = join('$$$',$cj_play_server_arr);
                $v['vod_play_note'] = join('$$$',$cj_play_note_arr);
                $v['vod_down_from'] = join('$$$',$cj_down_from_arr);
                $v['vod_down_url'] = join('$$$',$cj_down_url_arr);
                $v['vod_down_server'] = join('$$$',$cj_down_server_arr);
                $v['vod_down_note'] = join('$$$',$cj_down_note_arr);

                if(empty($v['vod_play_from'])) $v['vod_play_from']='';
                if(empty($v['vod_play_url'])) $v['vod_play_url']='';
                if(empty($v['vod_play_server'])) $v['vod_play_server']='';
                if(empty($v['vod_play_note'])) $v['vod_play_note']='';

                if(empty($v['vod_down_from'])) $v['vod_down_from']='';
                if(empty($v['vod_down_url'])) $v['vod_down_url']='';
                if(empty($v['vod_down_server'])) $v['vod_down_server']='';
                if(empty($v['vod_down_note'])) $v['vod_down_note']='';

                $info = model('Vod')->where($where)->find();
                
                
                if (!$info) {
                    $tmp = $this->syncImages($config['pic'],$v['vod_pic'],'vod');
                    $v['vod_pic'] = $tmp['pic'];
                    $msg = $tmp['msg'];
                                                           
                    $res = model('Vod')->insert($v);
                    if($res===false){

                    }
                    $color ='green';
                    $des= '新加入库，成功ok。';
                } else {
                    if(empty($config['uprule'])){
                        $des .= '没有设置任何二次更新项目，跳过。';
                        continue;
                    }
                    if ($info['vod_lock'] == 1) {
                        $des = '数据已经锁定，跳过。';
                    } else {
                        unset($v['vod_time_add']);

                        $update = [];
                        $ec=false;

                        if (strpos(',' . $config['uprule'], 'a') && !empty($v['vod_play_from'])) {
                            $old_play_from = $info['vod_play_from'];
                            $old_play_url = $info['vod_play_url'];
                            $old_play_server = $info['vod_play_server'];
                            $old_play_note = $info['vod_play_note'];
                            foreach ($cj_play_from_arr as $k2 => $v2) {
                                $cj_play_from = $v2;
                                $cj_play_url = $cj_play_url_arr[$k2];
                                $cj_play_server = $cj_play_server_arr[$k2];
                                $cj_play_note = $cj_play_note_arr[$k2];
                                if ($cj_play_url == $info['vod_play_url']) {
                                    $des .= '播放地址相同，跳过。';
                                } elseif (empty($cj_play_from)) {
                                    $des .= '播放器类型为空，跳过。';
                                } elseif (strpos("," . $info['vod_play_from'], $cj_play_from) <= 0) {
                                    $color = 'green';
                                    $des .= '播放组(' . $cj_play_from . ')，新增ok。';
                                    if(!empty($old_play_from)){
                                        $old_play_url .="$$$";
                                        $old_play_from .= "$$$" ;
                                        $old_play_server .= "$$$" ;
                                        $old_play_note .= "$$$" ;
                                    }
                                    $old_play_url .= "" . $cj_play_url;
                                    $old_play_from .= "" . $cj_play_from;
                                    $old_play_server .= "" . $cj_play_server;
                                    $old_play_note .= "" . $cj_play_note;
                                    $ec=true;
                                } else {
                                    $arr1 = explode("$$$", $old_play_url);
                                    $arr2 = explode("$$$", $old_play_from);
                                    $play_key = array_search($cj_play_from, $arr2);
                                    if ($arr1[$play_key] == $cj_play_url) {
                                        $des .= '播放组(' . $cj_play_from . ')，无需更新。';
                                    } else {
                                        $color = 'green';
                                        $des .= '播放组(' . $cj_play_from . ')，更新ok。';
                                        $arr1[$play_key] = $cj_play_url;
                                        $ec=true;
                                    }
                                    $old_play_url = join('$$$', $arr1);
                                }
                            }
                            if($ec) {
                                $update['vod_play_from'] = $old_play_from;
                                $update['vod_play_url'] = $old_play_url;
                                $update['vod_play_server'] = $old_play_server;
                                $update['vod_play_note'] = $old_play_note;
                            }
                        }

                        $ec=false;
                        if (strpos(',' . $config['uprule'], 'b') && !empty($v['vod_down_from'])) {
                            $old_down_from = $info['vod_down_from'];
                            $old_down_url = $info['vod_down_url'];
                            $old_down_server = $info['vod_down_server'];
                            $old_down_note = $info['vod_down_note'];

                            foreach ($cj_down_from_arr as $k2 => $v2) {
                                $cj_down_from = $v2;
                                $cj_down_url = $cj_down_url_arr[$k2];
                                $cj_down_server = $cj_down_server_arr[$k2];
                                $cj_down_note = $cj_down_note_arr[$k2];


                                if ($cj_down_url == $info['vod_down_url']) {
                                    $des .= '下载地址相同，跳过。';
                                } elseif (empty($cj_down_from)) {
                                    $des .= '下载器类型为空，跳过。';
                                } elseif (strpos("," . $info['vod_down_from'], $cj_down_from)===false) {
                                    $color = 'green';
                                    $des .= '下载组(' . $cj_down_from . ')，新增ok。';
                                    if(!empty($old_down_from)){
                                        $old_down_url .="$$$";
                                        $old_down_from .= "$$$" ;
                                        $old_down_server .= "$$$" ;
                                        $old_down_note .= "$$$" ;
                                    }

                                    $old_down_url .= $cj_down_url;
                                    $old_down_from .= $cj_down_from;
                                    $old_down_server .= $cj_down_server;
                                    $old_down_note .= $cj_down_note;
                                    $ec=true;
                                } else {
                                    $arr1 = explode("$$$", $old_down_url);
                                    $arr2 = explode("$$$", $old_down_from);
                                    $down_key = array_search($cj_down_from, $arr2);
                                    if ($arr1[$down_key] == $cj_down_url) {
                                        $des .= '下载组(' . $cj_down_from . ')，无需更新。';
                                    } else {
                                        $color = 'green';
                                        $des .= '下载组(' . $cj_down_from . ')，更新ok。';
                                        $arr1[$down_key] = $cj_down_url;
                                        $ec=true;
                                    }
                                    $old_down_url = join('$$$', $arr1);
                                }
                            }

                            if($ec) {
                                $update['vod_down_from'] = $old_down_from;
                                $update['vod_down_url'] = $old_down_url;
                                $update['vod_down_server'] = $old_down_server;
                                $update['vod_down_note'] = $old_down_note;
                            }
                        }

                        if (strpos(',' . $config['uprule'], 'c') && !empty($v['vod_serial']) && $v['vod_serial']!=$info['vod_serial']) {
                            $update['vod_serial'] = $v['vod_serial'];
                        }
                        if (strpos(',' . $config['uprule'], 'd') && !empty($v['vod_remarks']) && $v['vod_remarks']!=$info['vod_remarks']) {
                            $update['vod_remarks'] = $v['vod_remarks'];
                        }
                        if (strpos(',' . $config['uprule'], 'e') && !empty($v['vod_director']) && $v['vod_director']!=$info['vod_director']) {
                            $update['vod_director'] = $v['vod_director'];
                        }
                        if (strpos(',' . $config['uprule'], 'f') && !empty($v['vod_actor']) && $v['vod_actor']!=$info['vod_actor']) {
                            $update['vod_actor'] = $v['vod_actor'];
                        }
                        if (strpos(',' . $config['uprule'], 'g') && !empty($v['vod_year']) && $v['vod_year']!=$info['vod_year']) {
                            $update['vod_year'] = $v['vod_year'];
                        }
                        if (strpos(',' . $config['uprule'], 'h') && !empty($v['vod_area']) && $v['vod_area']!=$info['vod_area']) {
                            $update['vod_area'] = $v['vod_area'];
                        }
                        if (strpos(',' . $config['uprule'], 'i') && !empty($v['vod_lang']) && $v['vod_lang']!=$info['vod_lang']) {
                            $update['vod_lang'] = $v['vod_lang'];
                        }
                        if (strpos(',' . $config['uprule'], 'j') && substr($info["vod_pic"], 0, 4) == "http" && $v['vod_pic']!=$info['vod_pic']) {
                            $tmp = $this->syncImages($config['pic'],$v['vod_pic'],'vod');
                            $update['vod_pic'] =$tmp['pic'];
                            $msg =$tmp['msg'];
                        }
                        if (strpos(',' . $config['uprule'], 'k') && !empty($v['vod_content']) && $v['vod_content']!=$info['vod_content']) {
                            $update['vod_content'] = $v['vod_content'];
                        }
                        if (strpos(',' . $config['uprule'], 'l') && !empty($v['vod_tag']) && $v['vod_tag']!=$info['vod_tag']) {
                            $update['vod_tag'] = $v['vod_tag'];
                        }
                        if (strpos(',' . $config['uprule'], 'm') && !empty($v['vod_sub']) && $v['vod_sub']!=$info['vod_sub']) {
                            $update['vod_sub'] = $v['vod_sub'];
                        }
                        if (strpos(',' . $config['uprule'], 'n') && !empty($v['vod_class']) && $v['vod_class']!=$info['vod_class']) {
                            $update['vod_class'] = mac_txt_merge($info['vod_class'], $v['vod_class']);
                        }

                        if (count($update) > 0) {
                            $update['vod_time'] = time();

                            $where = [];
                            $where['vod_id'] = $info['vod_id'];
                            $res = model('Vod')->where($where)->update($update);
                            if ($res === false) {

                            }
                        }

                    }
                }
            }
            if($show==1) {
                mac_echo( ($k + 1) .'、'. $v['vod_name'] . "<font color=$color>" .$des .'</font>'. $msg.'' );
            }
            else{
                return ['code'=>($color=='red' ? 1001 : 1),'msg'=>$des ];
            }
        }

        if(ENTRANCE=='api'){
            Cache::rm('collect_break_vod');
            if ($data['page']['page'] < $data['page']['pagecount']) {
                $param['page'] = intval($data['page']['page']) + 1;
                $res = $this->vod($param);
                if($res['code']>1){
                    return $this->error($res['msg']);
                }
                $this->vod_data($param,$res );
            }
            mac_echo("数据采集完成");
            die;
        }

        if($show==1) {
            if ($param['ac'] == 'cjsel') {
                Cache::rm('collect_break_vod');
                mac_echo("数据采集完成");
                unset($param['page'],$param['ids']);
                $param['ac'] = 'list';
                $url = url('api') . '?' . http_build_query($param);
                mac_jump($url, 3);
            } else {
                if ($data['page']['page'] >= $data['page']['pagecount']) {
                    Cache::rm('collect_break_vod');
                    mac_echo("数据采集完成");
                    unset($param['page'],$param['ids']);
                    $param['ac'] = 'list';
                    $url = url('api') . '?' . http_build_query($param);
                    mac_jump($url, 3);
                } else {
                    $param['page'] = intval($data['page']['page']) + 1;
                    $url = url('api') . '?' . http_build_query($param);
                    mac_jump($url, 1);
                }
            }
        }
    }


    public function art_json($param)
    {
        $url_param = [];
        $url_param['ac'] = $param['ac'];
        $url_param['t'] = $param['t'];
        $url_param['pg'] = is_numeric($param['page']) ? $param['page'] : '';
        $url_param['h'] = $param['h'];
        $url_param['ids'] = $param['ids'];
        $url_param['wd'] = $param['wd'];

        if($param['ac']!='list'){
            $url_param['ac'] = 'detail';
        }

        $url = $param['cjurl'];
        if(strpos($url,'?')===false){
            $url .='?';
        }
        else{
            $url .='&';
        }
        $html = mac_curl_get($url) . base64_decode($param['param']);
        if(empty($html)){
            return ['code'=>1001, 'msg'=>'连接API资源库失败，通常为服务器网络不稳定或禁用了采集'];
        }

        $json = json_decode($html,true);
        if(!$json){
            return ['code'=>1002, 'msg'=>'JSON格式不正确，不支持采集'];
        }

        $array_page = [];
        $array_page['page'] = $json['page'];
        $array_page['pagecount'] = $json['pagecount'];
        $array_page['pagesize'] = $json['limit'];
        $array_page['recordcount'] = $json['total'];
        $array_page['url'] = $url;

        $type_list = model('Type')->getCache('type_list');
        $bind_list = config('bind');

        $key = 0;
        $array_data = [];
        foreach($json['list'] as $key=>$v){
            $array_data[$key] = $v;
            $bind_key = $param['cjflag'] .'_'.$v['type_id'];
            if($bind_list[$bind_key] >0){
                $array_data[$key]['type_id'] = $bind_list[$bind_key];
            }
            else{
                $array_data[$key]['type_id'] = 0;
            }
        }

        $array_type = [];
        $key=0;
        //分类列表
        if($param['ac'] == 'list'){
            foreach($json['class'] as $k=>$v){
                $array_type[$key]['type_id'] = $v['type_id'];
                $array_type[$key]['type_name'] = $v['type_name'];
                $key++;
            }
        }

        $res = ['code'=>1, 'msg'=>'ok', 'page'=>$array_page, 'type'=>$array_type, 'data'=>$array_data ];
        return $res;
    }

    public function art_data($param,$data,$show=1)
    {
        if($show==1) {
            mac_echo('当前采集任务<strong class="green">' . $data['page']['page'] . '</strong>/<span class="green">' . $data['page']['pagecount'] . '</span>页 采集地址&nbsp;' . $data['page']['url'] . '');
        }

        $config = config('maccms.collect');
        $config = $config['art'];

        $type_list = model('Type')->getCache('type_list');

        foreach($data['data'] as $k=>$v){

            $color='red';
            $des='';
            if($v['type_id'] ==0){
                $des = '分类未绑定，跳过err';
            }
            elseif(empty($v['art_name'])) {
                $des = '数据不完整，跳过err';
            }
            elseif(strpos(','.$config['filter'],$v['art_name'])) {
                $des = '数据在过滤单中，跳过err';
            }
            else {
                unset($v['art_id']);

                $v['type_id_1'] = intval($type_list[$v['type_id']]['type_pid']);
                $v['art_en'] = Pinyin::get($v['art_name']);
                $v['art_letter'] = strtoupper(substr($v['art_en'],0,1));
                $v['art_time_add'] = time();
                $v['art_time'] = time();
                $v['art_status'] = $config['status'];

                $v['art_lock'] = intval($v['art_lock']);
                $v['art_status'] = intval($v['art_status']);

                $v['art_level'] = intval($v['art_level']);
                $v['art_hits'] = intval($v['art_hits']);
                $v['art_hits_day'] = intval($v['art_hits_day']);
                $v['art_hits_week'] = intval($v['art_hits_week']);
                $v['art_hits_month'] = intval($v['art_hits_month']);
                $v['art_stint'] = intval($v['art_stint']);

                $v['art_up'] = intval($v['art_up']);
                $v['art_down'] = intval($v['art_down']);

                $v['art_score'] = floatval($v['art_score']);
                $v['art_score_all'] = intval($v['art_score_all']);
                $v['art_score_num'] = intval($v['art_score_num']);

                if($config['hits_start']>0 && $config['hits_end']>0) {
                    $v['art_hits'] = rand($config['hits_start'], $config['hits_end']);
                    $v['art_hits_day'] = rand($config['hits_start'], $config['hits_end']);
                    $v['art_hits_week'] = rand($config['hits_start'], $config['hits_end']);
                    $v['art_hits_month'] = rand($config['hits_start'], $config['hits_end']);
                }

                if($config['updown_start']>0 && $config['updown_end']){
                    $v['art_up'] = rand($config['updown_start'], $config['updown_end']);
                    $v['art_down'] = rand($config['updown_start'], $config['updown_end']);
                }

                if($config['score']==1) {
                    $v['art_score_num'] = rand(1, 1000);
                    $v['art_score_all'] = $v['art_score_num'] * rand(1, 10);
                    $v['art_score'] = round($v['art_score_all'] / $v['art_score_num'], 1);
                }

                if ($config['psernd'] == 1) {
                    $v['art_content'] = mac_rep_pse_rnd($config['words'], $v['art_content']);
                }
                if ($config['psesyn'] == 1) {
                    $v['art_content'] = mac_rep_pse_syn($config['thesaurus'], $v['art_content']);
                }

                if(empty($v['art_blurb'])){
                    $v['art_blurb'] = mac_substring( strip_tags($v['art_content']) ,100);
                }

                $where = [];
                $where['art_name'] = $v['art_name'];
                if (strpos($config['inrule'], 'b')) {
                    $where['type_id'] = $v['type_id'];
                }

                //验证地址
                $cj_title_arr = explode('$$$',$v['art_title'] );
                $cj_note_arr = explode('$$$',$v['art_note']);
                $cj_content_arr = explode('$$$',$v['art_content']);

                $tmp_title_arr=[];
                $tmp_note_arr=[];
                $tmp_content_arr=[];
                foreach($cj_content_arr as $kk=>$vv){
                    $tmp_content_arr[] = $vv;
                    $tmp_title_arr[] = $cj_title_arr[$kk];
                    $tmp_note_arr[] = $cj_note_arr[$kk];
                }
                $v['art_title'] = join('$$$',$tmp_title_arr);
                $v['art_note'] = join('$$$',$tmp_note_arr);
                $v['art_content'] = join('$$$',$tmp_content_arr);


                $info = model('Art')->where($where)->find();
                if (!$info) {
                    $tmp = $this->syncImages($config['pic'],$v['art_pic'],'art');
                    $v['art_pic'] = $tmp['pic'];
                    $msg = $tmp['msg'];
                    $res = model('Art')->insert($v);
                    if($res===false){

                    }
                    $color ='green';
                    $des= '新加入库，成功。';
                } else {

                    if ($info['art_lock'] == 1) {
                        $des = '数据已经锁定，跳过。';
                    } else {
                        unset($v['art_time_add']);

                        $old_art_title = $info['art_title'];
                        $old_art_note = $info['art_note'];
                        $old_art_content = $info['art_content'];

                        $cj_art_title = $v['art_title'];
                        $cj_art_note = $v['art_note'];
                        $cj_art_content = $v['art_content'];

                        if($old_art_title==$cj_art_title && $old_art_note==$cj_art_note && $old_art_content==$cj_art_content){
                            $des .= '详细介绍相同，跳过。';
                            continue;
                        }
                        else{
                            $rc=true;
                        }

                        if($rc){
                            $update=[];
                            if(empty($info["art_pic"]) || substr($info["art_pic"],0,4)=='http') { }

                            if(strpos(','.$config['uprule'],'b') && !empty($v['act_author']) && $v['vod_class']!=$info['vod_class']){
                                $update['art_author'] = $v['art_author'];
                            }
                            if(strpos(','.$config['uprule'],'c') && !empty($v['art_from']) && $v['art_from']!=$info['art_from']){
                                $update['art_from'] = $v['art_from'];
                            }
                            if(strpos(','.$config['uprule'],'d') && substr($info["art_pic"], 0, 4) == "http" && $v['art_pic']!=$info['art_pic']){
                                $tmp = $this->syncImages($config['pic'],$v['art_pic'],'art');
                                $update['art_pic'] =$tmp['pic'];
                                $msg =$tmp['msg'];
                            }
                            if(strpos(','.$config['uprule'],'e') && !empty($v['art_tag']) && $v['art_tag']!=$info['art_tag']){
                                $update['art_tag'] = $v['art_tag'];
                            }

                            if(count($update)>0){
                                $update['art_time'] = time();
                                $where = [];
                                $where['art_id'] = $info['art_id'];
                                $res = model('Art')->where($where)->update($update);
                                if($res===false){

                                }
                            }
                        }

                    }
                }
            }
            if($show==1) {
                mac_echo( ($k + 1) . $v['art_name'] . "<font color=$color>" .$des .'</font>'. $msg . '');
            }
            else{
                return ['code'=>($color=='red' ? 1001 : 1),'msg'=> $v['art_name'] .' '.$des ];
            }
        }

        if(ENTRANCE=='api'){
            Cache::rm('collect_break_art');
            if ($data['page']['page'] < $data['page']['pagecount']) {
                $param['page'] = intval($data['page']['page']) + 1;
                $res = $this->art($param);
                if($res['code']>1){
                    return $this->error($res['msg']);
                }
                $this->art_data($param,$res );
            }
            mac_echo("数据采集完成");
            die;
        }

        if($show==1) {
            if ($param['ac'] == 'cjsel') {
                Cache::rm('collect_break_art');
                mac_echo("数据采集完成");
                unset($param['ids']);
                $param['ac'] = 'list';
                $url = url('api') . '?' . http_build_query($param);
                mac_jump($url, 3);
            } else {
                if ($data['page']['page'] >= $data['page']['pagecount']) {
                    Cache::rm('collect_break_art');
                    mac_echo("数据采集完成");
                    unset($param['page']);
                    $param['ac'] = 'list';
                    $url = url('api') . '?' . http_build_query($param);
                    mac_jump($url, 3);
                } else {
                    $param['page'] = intval($data['page']['page']) + 1;
                    $url = url('api') . '?' . http_build_query($param);
                    mac_jump($url, 1);
                }
            }
        }
    }



}