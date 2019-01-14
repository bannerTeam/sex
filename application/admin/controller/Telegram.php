<?php
namespace app\admin\controller;

class Telegram extends Base
{

    var $_pre;

    public function __construct()
    {
        parent::__construct();
        $this->_pre = 'ad';
    }

    public function index()
    {
        $param = input();
        $param['page'] = intval($param['page']) <1 ? 1 : $param['page'];
        $param['limit'] = intval($param['limit']) <1 ? $this->_pagesize : $param['limit'];
        
        $where['bot_chat_id'] = '616302550';
        $where['platform'] = 1;        
        $res = model('Telegram')->listData($where, 'timer_time desc',$param['page'],$param['limit']);
        
        $this->assign('list',$res['list']);
        $this->assign('total',$res['total']);
        $this->assign('page',$res['page']);
        $this->assign('limit',$res['limit']);
        
        $param['page'] = '{page}';
        $param['limit'] = '{limit}';
        $this->assign('param',$param);
        
        $this->assign('title', 'Telegram管理');
        return $this->fetch('admin@telegram/index');
    }

    public function del()
    {
        $param = input();
        
        $id = intval($param['id']);
        if (! is_numeric($id)) {
            return $this->error('删除失败，请重试!');
        }
        
        $where['id'] = $id;
        $res = model('Telegram')->delData($where);
        
        if ($res['code'] === 1) {
            return $this->success('删除成功!');
        }
        return $this->error('删除失败，请重试');
    }

    /**
     * 自动生成
     */
    public function generate()
    {
        $param = input();
        
        if (Request()->isPost()) {
            
            $minute = input('post.minute');
            
            $number = input('post.number');
            
            // 1.立即执行
            $status = input('post.status');
            
            if (! is_numeric($minute) || ! is_numeric($number)) {
                return $this->error('失败!');
            }
            
            $where['vod_status'] = [
                'eq',
                1
            ];
            $res = model('Vod')->listData($where, 'vod_id desc', 1, $number);
            $list = $res['list'];
            foreach ($list as $k => $v) {
                $timer_time = time() + $k * intval($minute) * 60;
                self::timer($v, $timer_time);
            }
            
            if ($status === "1") {
                try {
                    set_time_limit(900);
                    file_get_contents('https://www.h18av.vip/chat/timer');
                } catch (Exception $e) {
                    ;
                }
            }
            
            return $this->success('保存成功!');
        }
        
        $this->assign('title', '任务生成');
        return $this->fetch('admin@telegram/generate');
        
        // var_dump($list);
        exit();
    }

    /**
     * *
     * 插入定时器任务
     * 
     * @param unknown $v            
     */
    private function timer($v, $timer_time)
    {
        
        //JUFD-905 情色旅馆中，美女用手淫让男人勃起并射精了 桐谷奈绪 (http://lcw8.xyz/vod/detail/id/8886.html)
        
        //群组、频道
        $chat_ids = ['-1001136791211','-1001432841135'];
        
        $model = model('Telegram');
        
        $weburl = 'http://lcw8.xyz/vod/detail/id/' . $v['vod_id'] . '.html';
                
        $data['bot_chat_id'] = '616302550';
        $data['bot_api_key'] = '616302550:AAER7vQwRYWk3A_pz0xOCGXAmGJq1dl1OWw';
        $data['bot_username'] = 'Lucaowan_Bot';
        $data['send_type'] = '2';
        $data['send_text'] = '<a href="' . $weburl . '">' . mac_substring($v['vod_name'],60) . '</a>
[撸草湾网址] http://lcw8.xyz/
[撸草湾频道] https://t.me/lucaowan
[撸草湾群组] https://t.me/luluwan2018';
        $data['send_photo'] = $v['vod_pic'];
        $data['send_video'] = '';
        $data['weburl'] = $weburl;        
        $data['timer_time'] = $timer_time;
        $data['send_time'] = 0;
        $data['send_status'] = 0;
        $data['platform'] = 1;
        
        
        foreach ($chat_ids as $k => $c) {
            $data['chat_id'] = $c;
            $res = $model->saveData($data);
        }        
        
    }
}
