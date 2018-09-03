<?php
namespace app\common\model;
use think\Db;

class User extends Base
{
    // 设置数据表（不含前缀）
    protected $name = 'user';

    // 定义时间戳字段名
    protected $createTime = '';
    protected $updateTime = '';

    // 自动完成
    protected $auto = [];
    protected $insert = [];
    protected $update = [];

    public $_guest_group = 1;
    public $_def_group = 2;

    public function countData($where)
    {
        $total = $this->where($where)->count();
        return $total;
    }

    public function listData($where, $order, $page, $limit = 20)
    {
        $total = $this->where($where)->count();
        $list = Db::name('User')->where($where)->order($order)->page($page)->limit($limit)->select();
        return ['code' => 1, 'msg' => '数据列表', 'page' => $page, 'pagecount' => ceil($total / $limit), 'limit' => $limit, 'total' => $total, 'list' => $list];
    }

    public function infoData($where, $field = '*')
    {
        if (empty($where) || !is_array($where)) {
            return ['code' => 1001, 'msg' => '参数错误'];
        }
        $info = $this->field($field)->where($where)->find();
        if (empty($info)) {
            return ['code' => 1002, 'msg' => '获取数据失败'];
        }
        $info = $info->toArray();

        //用户组
        $group_list = model('Group')->getCache('group_list');
        $info['group'] = $group_list[$info['group_id']];


        $info['user_pwd'] = '';
        return ['code' => 1, 'msg' => '获取成功', 'info' => $info];
    }

    public function saveData($data)
    {
        $validate = \think\Loader::validate('User');

        if (isset($data['user_start_time']) && !is_numeric($data['user_start_time'])) {
            $data['user_start_time'] = strtotime($data['user_start_time']);
        }
        if (isset($data['user_start_time']) && !is_numeric($data['user_end_time'])) {
            $data['user_end_time'] = strtotime($data['user_end_time']);
        }

        if (!empty($data['user_id'])) {
            if (!$validate->scene('edit')->check($data)) {
                return ['code' => 1001, 'msg' => '参数错误：' . $validate->getError()];
            }

            if (empty($data['user_pwd'])) {
                unset($data['user_pwd']);
            } else {
                $data['user_pwd'] = md5($data['user_pwd']);
            }
            $where = [];
            $where['user_id'] = ['eq', $data['user_id']];
            $res = $this->where($where)->update($data);
        } else {
            if (!$validate->scene('edit')->check($data)) {
                return ['code' => 1002, 'msg' => '参数错误：' . $validate->getError()];
            }

            $data['user_pwd'] = md5($data['user_pwd']);
            $res = $this->insert($data);
        }
        if (false === $res) {
            return ['code' => 1003, 'msg' => '' . $this->getError()];
        }
        return ['code' => 1, 'msg' => '保存成功'];
    }

    public function delData($where)
    {
        $res = $this->where($where)->delete();
        if ($res === false) {
            return ['code' => 1001, 'msg' => '删除失败' . $this->getError()];
        }
        return ['code' => 1, 'msg' => '删除成功'];
    }

    public function fieldData($where, $col, $val)
    {
        if (!isset($col) || !isset($val)) {
            return ['code' => 1001, 'msg' => '参数错误'];
        }
        $data = [];
        $data[$col] = $val;
        $res = $this->where($where)->update($data);
        if ($res === false) {
            return ['code' => 1002, 'msg' => '设置失败' . $this->getError()];
        }
        return ['code' => 1, 'msg' => '设置成功'];
    }

    public function register($param)
    {
        $config = config('maccms');

        $data = [];
        $data['user_name'] = htmlspecialchars(urldecode(trim($param['user_name'])));
        $data['user_pwd'] = htmlspecialchars(urldecode(trim($param['user_pwd'])));
        $data['user_pwd2'] = htmlspecialchars(urldecode(trim($param['user_pwd2'])));
        $data['verify'] = $param['verify'];

        $uid = $param['uid'];

        if ($config['user']['status'] == 0 || $config['user']['reg_open'] == 0) {
            return ['code' => 1001, 'msg' => '未开放注册'];
        }
        if (empty($data['user_name']) || empty($data['user_pwd']) || empty($data['user_pwd2'])) {
            return ['code' => 1002, 'msg' => '请填写必填项'];
        }
        if (!captcha_check($data['verify'])) {
            return ['code' => 1003, 'msg' => '验证码错误'];
        }
        if ($data['user_pwd'] != $data['user_pwd2']) {
            return ['code' => 1004, 'msg' => '密码与确认密码不一致'];
        }
        $row = $this->where('user_name', $data['user_name'])->find();
        if (!empty($row)) {
            return ['code' => 1005, 'msg' => '用户名已被注册，请更换'];
        }

        if (!preg_match("/^[a-zA-Z\d]*$/i", $data['user_name'])) {
            return ['code' => 1006, 'msg' => '用户名只能包含字母和数字，请更换'];
        }

        $validate = \think\Loader::validate('User');
        if (!$validate->scene('add')->check($data)) {
            return ['code' => 1007, 'msg' => '参数错误：' . $validate->getError()];
        }
        $fields = [];
        $fields['user_name'] = $data['user_name'];
        $fields['user_pwd'] = md5($data['user_pwd']);
        $fields['group_id'] = $this->_def_group;
        $fields['user_points'] = intval($config['user']['reg_points']);
        $fields['user_status'] = intval($config['user']['reg_status']);
        $fields['user_reg_time'] = time();

        $res = $this->insert($fields);
        if ($res === false) {
            return ['code' => 1010, 'msg' => '注册失败'];
        }

        $uid = intval($uid);
        if ($uid > 0 && $config['user']['invite_reg_points'] > 0) {
            $where = [];
            $where['user_id'] = $uid;
            $invite = $this->where($where)->find();
            if ($invite) {
                $update = [];
                $update['user_points'] = $invite['user_points'] + intval($config['user']['invite_reg_points']);
                $r = $this->where($where)->update($update);
            }
        }
        return ['code' => 1, 'msg' => '注册成功,请登录去会员中心完善个人信息'];
    }

    public function regcheck($t, $str)
    {
        $where = [];
        if ($t == 'user_name') {
            $where['user_name'] = $str;
            $row = $this->where($where)->find();
            if (!empty($row)) {
                return ['code' => 1001, 'msg' => '已注册'];
            }
        } elseif ($t == 'user_email') {
            $where['user_email'] = $str;
            $row = $this->where($where)->find();
            if (!empty($row)) {
                return ['code' => 1001, 'msg' => '已注册'];
            }
        } elseif ($t == 'verify') {
            if (!captcha_check($str)) {
                return ['code' => 1002, 'msg' => '验证码错误'];
            }
        }
        return ['code' => 1, 'msg' => '填写正确'];
    }

    public function info($param)
    {
        if (empty($param['user_pwd'])) {
            $this->error('请输入原密码');
            return;
        }
        if (md5($param['user_pwd']) != $GLOBALS['user']['user_pwd']) {
            $this->error('原密码错误');
            return;
        }
        if ($param['user_pwd1'] != $param['user_pwd2']) {
            $this->error('两次输入的新密码不一致');
            return;
        }

        $data = [];
        $data['user_id'] = $GLOBALS['user']['user_id'];
        $data['user_name'] = $GLOBALS['user']['user_name'];
        $data['user_qq'] = htmlspecialchars(urldecode(trim($param['user_qq'])));
        $data['user_question'] = htmlspecialchars(urldecode(trim($param['user_question'])));
        $data['user_answer'] = htmlspecialchars(urldecode(trim($param['user_answer'])));
        if (!empty($param['user_pwd2'])) {
            $data['user_pwd'] = htmlspecialchars(urldecode(trim($param['user_pwd2'])));
        }
        return model('User')->saveData($data);
    }

    public function login($param)
    {
        $data = [];
        $data['user_name'] = htmlspecialchars(urldecode(trim($param['user_name'])));
        $data['user_pwd'] = htmlspecialchars(urldecode(trim($param['user_pwd'])));
        $data['verify'] = $param['verify'];

        if (empty($data['openid'])) {
            if (empty($data['user_name']) || empty($data['user_pwd']) || empty($data['verify'])) {
                return ['code' => 1001, 'msg' => '请填写必填项'];
            }

            if (!captcha_check($data['verify'])) {
                return ['code' => 1002, 'msg' => '验证码错误'];
            }

            $pwd = md5($data['user_pwd']);
            $where = [];

            $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
            if (!preg_match($pattern, $data['user_name'])) {
                $where['user_name'] = ['eq', $data['user_name']];
            } else {
                $where['user_email'] = ['eq', $data['user_name']];
            }

            $where['user_pwd'] = ['eq', $pwd];
        } else {
            if (empty($data['openid']) || empty($data['col'])) {
                return ['code' => 1001, 'msg' => '请填写必填项'];
            }
            $where[$data['col']] = $data['openid'];
        }
        $where['user_status'] = ['eq', 1];
        $row = $this->where($where)->find();

        if (empty($row)) {
            return ['code' => 1003, 'msg' => '查找用户信息失败'];
        }

        if ($row['user_end_time'] < time()) {
            $update['group_id'] = 2;
        }

        $random = md5(rand(10000000, 99999999));
        $update['user_random'] = $random;
        $update['user_login_ip'] = ip2long(request()->ip());
        $update['user_login_time'] = time();
        $update['user_login_num'] = $row['user_login_num'] + 1;
        $update['user_last_login_time'] = $row['user_login_time'];
        $update['user_last_login_ip'] = $row['user_login_ip'];

        $res = $this->where($where)->update($update);
        if ($res === false) {
            return ['code' => 1004, 'msg' => '更新登录信息失败'];
        }

        //用户组
        $group_list = model('Group')->getCache('group_list');
        $group = $group_list[$row['group_id']];

        cookie('user_id', $row['user_id']);
        cookie('user_name', $row['user_name']);
        cookie('group_id', $group['group_id']);
        cookie('group_name', $group['group_name']);
        cookie('user_check', md5($random . '-' . $row['user_id'] . '-' . $row['group_id']));
        cookie('user_portrait', mac_get_user_portrait($row['user_id']));

        return ['code' => 1, 'msg' => '登录成功'];
    }

    public function logout()
    {
        cookie('user_id', null);
        cookie('user_name', null);
        cookie('group_id', null);
        cookie('group_name', null);
        cookie('user_check', null);
        cookie('user_portrait', null);
        return ['code' => 1, 'msg' => '退出成功'];
    }

    public function checkLogin()
    {
        $user_id = cookie('user_id');
        $user_name = cookie('user_name');
        $user_check = cookie('user_check');

        if (empty($user_id) || empty($user_name) || empty($user_check)) {
            return ['code' => 1001, 'msg' => '未登录'];
        }

        $where = [];
        $where['user_id'] = $user_id;
        $where['user_name'] = $user_name;
        $where['user_status'] = 1;

        $info = $this->field('*')->where($where)->find();
        if (empty($info)) {
            return ['code' => 1002, 'msg' => '未登录'];
        }
        $info = $info->toArray();
        $login_check = md5($info['user_random'] . '-' . $info['user_id'] . '-' . $info['group_id']);
        if ($login_check != $user_check) {
            return ['code' => 1003, 'msg' => '未登录'];
        }
        //用户组
        $group_list = model('Group')->getCache('group_list');
        $info['group'] = $group_list[$info['group_id']];

        return ['code' => 1, 'msg' => '已登录', 'info' => $info];
    }

    public function promoter($data)
    {
        if (empty($data['uid'])) {
            return ['code' => 1001, 'msg' => '参数错误'];
        }

        $config = config('maccms.user');
        if ($config['market_status'] == 0) {
            return ['code' => 1002, 'msg' => '未开启推广积分'];
        }

        $where = [];
        $where['user_id'] = $data['uid'];
        $res = $this->infoData($where);
        if ($res['code'] > 1) {
            return $res;
        }

        $where2 = [];
        $where2['user_id'] = $data['uid'];
        $where2['visit_ip'] = ip2long(request()->ip());
        $where2['visit_time'] = ['gt', strtotime(date('Y-m-d'))];
        $cc = Db::name('visit')->where($where2)->count();
        if ($cc > 0) {
            return ['code' => 1003, 'msg' => '重复推广不积分'];
        }
        $where2['visit_time'] = time();
        Db::name('visit')->insert($where2);

        $info = $res['info'];
        $update['user_points'] = $info['user_points'] + $config['market'];
        $this->where($where)->update($update);

    }

    public function resetPwd()
    {

    }

    public function findpass($param)
    {
        $data = [];
        $data['user_name'] = htmlspecialchars(urldecode(trim($param['user_name'])));
        $data['user_question'] = htmlspecialchars(urldecode(trim($param['user_question'])));
        $data['user_answer'] = htmlspecialchars(urldecode(trim($param['user_answer'])));
        $data['user_pwd'] = htmlspecialchars(urldecode(trim($param['user_pwd'])));
        $data['user_pwd2'] = htmlspecialchars(urldecode(trim($param['user_pwd2'])));
        $data['verify'] = $param['verify'];

        if (empty($data['user_name']) || empty($data['user_question']) || empty($data['user_answer']) || empty($data['user_pwd']) || empty($data['user_pwd2']) || empty($data['verify'])) {
            return ['code' => 1001, 'msg' => '参数错误'];
        }

        if (!captcha_check($data['verify'])) {
            return ['code' => 1002, 'msg' => '验证码错误'];
        }

        if ($data['user_pwd'] != $data['user_pwd2']) {
            return ['code' => 1003, 'msg' => '二次密码不一致'];
        }


        $where = [];
        $where['user_name'] = $data['user_name'];
        $where['user_question'] = $data['user_question'];
        $where['user_answer'] = $data['user_answer'];

        $info = $this->where($where)->find();
        if (empty($info)) {
            return ['code' => 1004, 'msg' => '获取用户失败，账号、问题、答案可能不正确'];
        }

        $update = [];
        $update['user_pwd'] = md5($data['user_pwd']);

        $where = [];
        $where['user_id'] = $info['user_id'];
        $res = $this->where($where)->update($update);

        if (false === $res) {
            return ['code' => 1005, 'msg' => '' . $this->getError()];
        }
        return ['code' => 1, 'msg' => '密码找回成功成功'];

    }

    public function popedom($type_id, $popedom, $group_id = 1)
    {
        $group_list = model('Group')->getCache();
        $group_info = $group_list[$group_id];

        if (strpos(',' . $group_info['group_type'], ',' . $type_id . ',') !== false && !empty($group_info['group_popedom'][$type_id][$popedom]) !== false) {
            return true;
        }
        return false;
    }

    public function upgrade($param)
    {
        $group_id = intval($param['group_id']);
        $long = $param['long'];
        $points_long = ['day'=>86400,'week'=>86400*7,'month'=>86400*30,'year'=>86400*365];

        if (!array_key_exists($long, $points_long)) {
            return ['code'=>1001,'msg'=>'非法操作'];
        }

        if($group_id <3){
            return ['code'=>1002,'msg'=>'请选择自定义收费会员组'];
        }

        $group_list = model('Group')->getCache();
        $group_info = $group_list[$group_id];
        if(empty($group_info)){
            return ['code'=>1003,'msg'=>'获取会员组信息失败'];
        }

        $point = $group_info['group_points_'.$long];
        if($GLOBALS['user']['user_points'] < $point){
            return ['code'=>1004,'msg'=>'积分不够，无法升级'];
        }

        $sj = $points_long[$long];
        $end_time = time() + $sj;
        if($GLOBALS['user']['user_end_time'] > time() ){
            $end_time = $GLOBALS['user']['user_end_time'] + $sj;
        }

        $where = [];
        $where['user_id'] = $GLOBALS['user']['user_id'];

        $data = [];
        $data['user_points'] = $GLOBALS['user']['user_points'] - $point;
        $data['user_end_time'] = $end_time;
        $data['group_id'] = $group_id;

        $res = $this->where($where)->update($data);
        if($res===false){
            return ['code'=>1009,'msg'=>'升级会员组失败'];
        }
        return ['code'=>1,'msg'=>'升级会员组成功'];
    }

    public function bind($param)
    {
        $param['to'] = htmlspecialchars(urldecode(trim($param['to'])));
        $param['code'] = htmlspecialchars(urldecode(trim($param['code'])));

        if(!in_array($param['ac'],['email','phone']) || empty($param['to']) || empty($param['code'])){
            return ['code'=>2001,'msg'=>'参数错误'];
        }

        $stime = strtotime('-5 min');
        $where=[];
        $where['user_id'] = $GLOBALS['user']['user_id'];
        $where['msg_time'] = ['gt',$stime];
        $where['msg_code'] = ['eq',$param['code']];
        $where['msg_type'] = ['eq', ($param['ac']=='mobile' ? 2 : 1) ];
        $res = model('msg')->infoData($where);
        if($res['code'] >1){
            return ['code'=>2002,'msg'=>'验证信息错误，请重试'];
        }

        $where=[];
        $where['user_email'] = ['eq',$param['to']];
        $update=[];
        $update['user_email'] = '';
        $res = $this->where($where)->update($update);
        if($res['code']===false){

        }

        $update=[];
        $update['user_email'] = $param['to'];
        $where=[];
        $where['user_id'] = $GLOBALS['user']['user_id'];
        $res = $this->where($where)->update($update);
        if($res===false){
            return ['code'=>2003,'msg'=>'更新用户信息失败，请重试'];
        }
        return ['code'=>1,'msg'=>'绑定成功'];
    }

    public function unbind($param)
    {
        if(!in_array($param['ac'],['email','phone']) ){
            return ['code'=>2001,'msg'=>'参数错误'];
        }
        $col = 'user_email';
        if($param['ac']=='phone'){
            $col = 'user_phone';
        }
        $update=[];
        $update[$col] = '';
        $where=[];
        $where['user_id'] = $GLOBALS['user']['user_id'];
        $res = $this->where($where)->update($update);
        if($res===false){
            return ['code'=>2002,'msg'=>'更新用户信息失败，请重试'];
        }
        return ['code'=>1,'msg'=>'解绑成功'];
    }

    public function bindmsg($param)
    {
        $param['to'] = htmlspecialchars(urldecode(trim($param['to'])));
        $param['code'] = htmlspecialchars(urldecode(trim($param['code'])));

        if(!in_array($param['ac'],['email','phone']) || empty($param['to'])){
            return ['code'=>2001,'msg'=>'参数错误'];
        }
        $to = $param['to'];
        $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
        if(!preg_match( $pattern, $to)){
            return ['code'=>2002,'msg'=>'邮箱地址格式不正确'];
        }
        $code = mac_get_rndstr(6,'num');
        $title = '【'.$GLOBALS['config']['site']['site_name'].'】绑定邮箱验证';
        $msg = '【'.$GLOBALS['config']['site']['site_name'].'】的会员您好，'.$GLOBALS['user']['user_name']."<br>".'邮箱验证码为：'. $code .',请在5分钟内完整验证。' ;

        $r=0;
        if($param['ac']=='email'){
            $stime = strtotime('-5 min');
            $where=[];
            $where['user_id'] = $GLOBALS['user']['user_id'];
            $where['msg_time'] = ['gt',$stime];
            $where['msg_type'] = ['eq', ($param['ac']=='mobile' ? 2 : 1) ];
            $res = model('msg')->infoData($where);
            if($res['code'] ==1){
                return ['code'=>2003,'msg'=>'请不要频繁发送'];
            }

            $res = mac_send_mail($to, $title,$msg);
            if($res){
                $data=[];
                $data['user_id'] = $GLOBALS['user']['user_id'];
                $data['msg_type'] = 1;
                $data['msg_status'] = 0;
                $data['msg_to'] = $to;
                $data['msg_code'] = $code;
                $data['msg_content'] = $msg;
                $data['msg_time'] = time();
                $res = model('msg')->saveData($data);
                if($res['code']==1){
                    $r=1;
                }
            }
        }

        if($r==1){
            return ['code'=>1,'msg'=>'验证码发送成功，请前往查看'];
        }
        else{
            return ['code'=>2009,'msg'=>'验证码发送失败，请重试'];
        }
    }

}