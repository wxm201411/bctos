<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星 <xw@bctos.cn> <QQ:203163051>
// +----------------------------------------------------------------------
namespace app\common\model;

use app\common\model\Base;
use think\facade\Db;

/**
 * 用户模型
 */
class User extends Base
{

    public function initialize(): void
    {
        parent::initialize();
        $this->openCache = true; // 当前model开启getInfo的缓存功能
    }

    public function lists($status = 1, $order = 'uid DESC', $field = true)
    {
        $map = array(
            'status' => $status
        );
        return $this->field($field)
            ->where(wp_where($map))
            ->order($order)
            ->select();
    }

    /**
     * 注册一个新用户
     *
     * @param string $username
     *            用户名
     * @param string $password
     *            用户密码
     * @param string $email
     *            用户邮箱
     * @param string $mobile
     *            用户手机号码
     * @return integer 注册成功-用户信息，注册失败-错误编号
     */
    public function register($username, $password, $email, $mobile, $truename = '', $price = 0.2)
    {
        $data = array(
            'nickname' => $username,
            'login_name' => $mobile,
            'password' => think_bctos_md5($password),
            'email' => $email,
            'mobile' => $mobile,
            'truename' => $truename,
            'is_audit' => config('app.REG_AUDIT'),
            'reg_time' => time(),
            'reg_ip' => get_client_ip(),
            'sex' => 1,
            'level' => 1,
            'price' => $price
        );

        $uid = $this->insertGetId($data);
        return $uid ? $uid : 0; // 0-未知错误，大于0-注册成功
    }

    /*
     * 密码修改器
     */
    public function setPasswordAttr($password)
    {
        return think_bctos_md5($password);
    }

    public function addUser($data)
    {
        $res = $this->insertGetId($data);
        return $res;
    }

    /**
     * 登录指定用户
     *
     * @param integer $uid
     *            用户ID
     * @return boolean ture-登录成功，false-登录失败
     */
    public function login($username, $password, $from = 'user_login', $type = 1)
    {
        $username = safe($username, 'text');
        $password = safe($password, 'text');

        /* 检测是否在当前应用注册 */
        $map = [];
        switch ($type) {
            case 1:
                $map['mobile'] = $username;
                break;
            case 2:
                $map['email'] = $username;
                break;
            case 3:
                $map['nickname'] = $username;
                break;
            case 4:
                $map['id'] = $username;
                break;
            default:
                return 0; // 参数错误
        }

        /* 获取用户数据 */
        $user = $this->field(true)->where(wp_where($map))->find();

        if (!$user) {
            unset($map);
            $map['login_name'] = $username;
            $user = $this->field(true)->where(wp_where($map))->find();
        }

        if (intval($user['status']) > 0) {
            /* 验证用户密码 */
            //dump(think_bctos_md5($password));dump($user['password']);exit;
            if (think_bctos_md5($password) === $user['password']) {
                // 记录行为
                action_log($from, 'user', $user['uid'], $user['uid']);

                /* 登录用户 */
                $this->autoLogin($user);

                // 登录成功，返回用户ID
                return $user['uid'];
            } else {
                $this->error = '密码错误！';
                return 0;
            }
        } else {
            $this->error = '用户不存在或已被禁用！'; // 应用级别禁用
            return 0;
        }
    }

    /**
     * 注销当前用户
     *
     * @return void
     */
    public function logout()
    {
        $wpid = get_wpid();
        session('menu_default', null);
        session('mid', null);
        session('mid_0', null);
        session('user_auth', null);
        session('user_auth_sign', null);
        session('wpid', null);
        session('openid_' . $wpid, null);
        session('manager_id', null);

        cookie('user_id', null);
    }

    /**
     * 自动登录用户
     *
     * @param integer $user
     *            用户信息数组
     */
    public function autoLogin($user, $log = true)
    {
        /* 更新登录信息 */
        if ($log) {
            
            $data = array(
                'uid' => $user['uid'],
                'login_count' => Db::raw('login_count+1'),
                'last_login_time' => NOW_TIME,
                'last_login_ip' => get_client_ip(1)
            );
            $this->where('uid', $user['uid'])->save($data);
        }

        /* 记录登录SESSION和COOKIES */
        $auth = array(
            'uid' => $user['uid'],
            'username' => $user['nickname'],
            'last_login_time' => NOW_TIME
        );
        session('manager_id', $user['uid']);
        session('mid', $user['uid']);
 //        dump(intval(session('mid')));
//         dump(get_wpid());die;

        session('user_auth', $auth);
        session('user_auth_sign', data_auth_sign($auth));
    }

    public function getNickName($uid)
    {
        $info = $this->getUserInfo($uid);
        return $info['nickname'];
    }

    /**
     * 获取用户全部信息
     */
    public function getUserInfo($uid, $update = false)
    {
        if (!($uid > 0)) {
            return false;
        }

        $key = cache_key('uid:' . $uid, $this->name);
        $userInfo = S($key);

        if ($userInfo === false || $update || true) {
            // 获取用户基本信息
            $userInfo = $this->where('uid', $uid)->find();
            $userInfo = isset($userInfo) ? $userInfo->toArray() : [];
            // 公众号粉丝信息
            $userInfo['pbids'] = [];
            $pbids = M('public_follow')->where("uid='$uid'")
                ->field(true)
                ->select();

            foreach ($pbids as $t) {
                $userInfo['pbids'][$t['pbid']] = $t['openid'];
                $userInfo['remarks'][$t['pbid']] = $t['remark'];
                $userInfo['has_subscribe'][$t['pbid']] = $t['has_subscribe'];
            }

            // 是否为系统管理员
            $userInfo['is_root'] = is_administrator($uid);
            $userInfo['headimgurl'] = empty($userInfo['headimgurl']) ? __ROOT__ . '/static/face/default_head_50.png' : $userInfo['headimgurl'];

            $sexArr = array(
                0 => '保密',
                1 => '男',
                2 => '女'
            );
            $sexArr2 = array(
                0 => 'Ta',
                1 => '他',
                2 => '她'
            );
            $userInfo['sex'] = isset($userInfo['sex']) ? intval($userInfo['sex']) : '';
            $userInfo['sex_name'] = isset($sexArr[$userInfo['sex']]) ? $sexArr[$userInfo['sex']] : '';
            $userInfo['sex_alias'] = isset($sexArr2[$userInfo['sex']]) ? $sexArr2[$userInfo['sex']] : '';

            // 获取组信息
            $group_info = M('auth_group_access')->alias('a')
                ->join('auth_group s', 'a.group_id=s.id')
                ->where([
                    'a.uid' => $uid
                ])->select();
            $userInfo['groups'] = $group_info;

            //行业，爱好
//            $userInfo['industry_titles'] = $this->getCommonCategoryTitles($userInfo['industry']);
//            $userInfo['hobby_titles'] = $this->getCommonCategoryTitles($userInfo['hobby']);

            S($key, $userInfo, 86400);
        }

        $pbid = session('pbid');
        if ($pbid && !empty($userInfo['remarks'][$pbid])) {
            $userInfo['nickname'] = $userInfo['remarks'][$pbid];
        }

        //优先使用真实姓名
        empty($userInfo['truename']) || $userInfo['nickname'] = $userInfo['truename'];

        $userInfo['score_txt'] = wp_money_format($userInfo['score'] / 100);
        return $userInfo;
    }

    private function getCommonCategoryTitles($id_str)
    {
        $res = [];

        if (empty($id_str)) return $res;

        static $common_cates;
        if (empty($common_cates)) {
            $common_cates = M('common_category')->whereIn('app', ['ziyuanhui_hobby', 'ziyuanhui'])->column('title', 'id');
        }
        $arr = explode(',', $id_str);
        foreach ($arr as $id) {
            if (isset($common_cates[$id])) {
                $res[$id] = $common_cates[$id];
            }
        }

        return implode(', ', $res);
    }

    public function getUserInfoByOpenid($openid, $update = false)
    {
        $map['openid'] = $openid;
        $uid = M('public_follow')->where(wp_where($map))->value('uid');
        return $this->getUserInfo($uid, $update);
    }

    public function updateInfo($uid, $save)
    {
        if (empty($uid)) {
            return false;
        }

        $map['uid'] = $uid;
        $res = $this->where(wp_where($map))->update($save);
        if ($res !== false) {
            $this->getUserInfo($uid, true);
        }
        return $res;
    }

    /**
     * 更新用户信息
     *
     * @param int $uid
     *            用户id
     * @param string $password
     *            密码，用来验证
     * @param array $data
     *            修改的字段数组
     * @return true 修改成功，false 修改失败
     * @author huajie <banhuajie@163.com>
     */
    public function updateUserFields($uid, $password, $data)
    {
        if (empty($uid) || empty($password) || empty($data)) {
            $this->error = '参数错误！';
            return false;
        }

        // 更新前检查用户密码
        if (!$this->verifyUser($uid, $password)) {
            $this->error = '验证出错：密码不正确！';
            return false;
        }
        if (isset($data['password'])) {
            $data['password'] = think_bctos_md5($data['password']);
        }

        // 更新用户信息
        if ($data) {
            $res = $this->where('uid=' . $uid)->update($data);
            $this->getUserInfo($uid, true);
            return $res;
        }
        return false;
    }

    /**
     * 验证用户密码
     *
     * @param int $uid
     *            用户id
     * @param string $password_in
     *            密码
     * @return true 验证成功，false 验证失败
     * @author huajie <banhuajie@163.com>
     */
    protected function verifyUser($uid, $password_in)
    {
        // $password = $this->getFieldById ( $uid, 'password' );
        $map['uid'] = $uid;
        $password = $this->where(wp_where($map))->value('password');
        if (think_bctos_md5($password_in) === $password) {
            return true;
        }
        return false;
    }

    public function searchUserS($param = [])
    {
        if (empty($param)) {
            return 0;
        }

        // 搜索标签
        if (isset($param['tag_id']) && $param['tag_id']) {

            $uids = (array)M('auth_group_access')->where('tag_id', $param['tag_id'])->column('uid');
        }
        if (empty($uids)) {
            return 0;
        } else {
            return implode(',', $uids);
        }
    }

    public function searchUser($key, $openid = '')
    {
        if (empty($key)) {
            return 0;
        }

        if ($openid) {
            $uids = (array)M('public_follow')->where('openid', 'like', "%$openid%")->column('uid');
        } else {
            // 搜索用户表
            $where = "nickname LIKE '%$key%' OR truename LIKE '%$key%'";
            $uids = (array)$this->where(wp_where($where))->column('uid');
            // 搜索用户名备注
            $where2 = "remark LIKE '%$key%'";
            $uids2 = (array)M('public_follow')->where(wp_where($where2))->column('uid');

            // 搜索标签
            $where3 = "title LIKE '%$key%'";
            $tag_ids = (array)M('auth_group')->where(wp_where($where3))->column('id');
            $uids3 = [];
            if (!empty($tag_ids)) {
                $map['group_id'] = array(
                    'in',
                    $tag_ids
                );
                $uids3 = (array)M('auth_group_access')->where(wp_where($map))->column('uid');
            }

            $uids = array_unique(array_merge($uids, $uids2, $uids3));
        }

        if (empty($uids)) {
            return 0;
        } else {
            return implode(',', $uids);
        }
    }

    public function clearCache($id, $act_type = '', $uid = 0, $more_param = [])
    {
        $this->getUserInfo($id, true);
    }
}
