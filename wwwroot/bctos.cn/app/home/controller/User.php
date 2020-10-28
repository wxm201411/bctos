<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星 <xw@bctos.cn> <QQ:203163051>
// +----------------------------------------------------------------------
namespace app\home\controller;

use think\Validate;
use think\captcha\Captcha;
use think\Route;
use think\App;

/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class User extends Home
{

    public $statsArr = [
        0 => '冻结',
        1 => '正常'
    ];

    // 手机绑定登录
    public function wap_scan()
    {
        $key = I('key');

        get_wpid(DEFAULT_WPID);

        $isWeixinBrowser = isWeixinBrowser();
        if (!$isWeixinBrowser) {
            return $this->error('请在微信里打开');
        }
        $info = get_pbid_appinfo();
        $param['appid'] = $info['appid'];
        $callback = U('wap_scan', array(
            'key' => $key
        ));
        if ($_GET['state'] != 'weiphp') {
            $param['redirect_uri'] = $callback;
            $param['response_type'] = 'code';
            $param['scope'] = 'snsapi_userinfo';
            $param['state'] = 'weiphp';
            $info['is_bind'] && $param['component_appid'] = config('app.COMPONENT_APPID');
            $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?' . http_build_query($param) . '#wechat_redirect';
            header('Location: ' . $url);
            exit();
        } elseif ($_GET['state'] == 'weiphp') {
            if (empty($_GET['code'])) {
                exit('code获取失败');
            }

            $param['code'] = I('code');
            $param['grant_type'] = 'authorization_code';

            if ($info['is_bind']) {
                $param['appid'] = I('appid');
                $param['component_appid'] = config('app.COMPONENT_APPID');
                $param['component_access_token'] = D('public_bind/PublicBind')->_get_component_access_token();

                $url = 'https://api.weixin.qq.com/sns/oauth2/component/access_token?' . http_build_query($param);
            } else {
                $param['secret'] = $info['secret'];

                $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?' . http_build_query($param);
            }

            $content = wp_file_get_contents($url);
            $content = json_decode($content, true);
            if (!empty($content['errmsg'])) {
                exit($content['errmsg']);
            }

            $uid = D('common/Follow')->init_follow($content['openid'], $info['id']);
            $user = D('common/User')->getUserInfo($uid);

            S($key, $user, 120);

            return $this->fetch();
        }
    }

    public function register_edit()
    {
        if (!is_login()) {
            return $this->error('您还没有登陆', U('home/User/login'));
        }
        $myinfo = getUserInfo($this->mid);
        if (request()->isPost()) {
            /* 检测密码 */
            if ($myinfo['password'] != think_weiphp_md5(input('password'))) {
                return $this->error('密码输入错误！');
            }
            /* 检测验证码 */
            if (!captcha_check(input('verify'))) {
                return $this->error('验证码输入错误！');
            }
            $data['mobile'] = input('mobile');
            D('common/user')->updateUserFields($this->mid, input('password'), $data);

            return $this->success('修改成功', U('weixin/Publics/waitAudit'));
        }

        $this->assign('myinfo', $myinfo);
        return $this->fetch();
    }

    /* 注册页面 */
    public function register($username = '', $password = '', $repassword = '', $mobile = '', $email = '', $verify = '', $truename = '')
    {
        if (!config('app.USER_ALLOW_REGISTER')) {
            return $this->error('注册已关闭');
        }
        if (request()->isPost()) {
            // 注册用户
            $mobile = trim($mobile);
            $hasusername = D('common/User')->where('mobile', $mobile)->value('uid');

            /* 测试手机号 */
            if (empty($mobile)) {
                return $this->error('手机号码不能为空');
            } elseif (!preg_match('/^[1][3578][0-9]{9}$/', $mobile)) {
                return $this->error('手机格式不正确！');
            } elseif ($hasusername) {
                return $this->error('该手机已存在！');
            }
            empty($username) && $username = $mobile;

            /* 检测密码 */
            if (strlen($password) < 6 || strlen($password) > 30) {
                return $this->error('密码长度必须在6-30个字符之间！');
            }
            if ($password != $repassword) {
                return $this->error('密码和重复密码不一致！');
            }
            /* 测试邮箱 */
//            if (empty($email)) {
//                return $this->error('邮箱不能为空');
//            }
//            if (!preg_match('/^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/', $email)) {
//                return $this->error('邮箱格式不正确！');
//            }

            /* 检测验证码 */
            if (!captcha_check($verify)) {
                return $this->error('验证码输入错误！');
            }
            // CHECKOUT
            // $map ['code'] = I ( 'invite_code' );
            // if (empty ( $map ['code'] )) {
            // return $this->error ( '内测码不能为空！' );
            // }
            // if (! M( 'invite_code' )->where ( wp_where( $map ) )->find ()) {
            // return $this->error ( '内测码不正确！' );
            // }
            // echo D('common/User')->getlastsql();
            // dump($hasusername);exit;
            /* 调用注册接口注册用户 */
            $uid = D('common/User')->register($username, $password, $email, $mobile, $truename);

            if (0 < $uid) {
                $url = cookie('redirect_url');
                if ($url) {
                    cookie('redirect_url', null);
                } else {
                    $url = U('home/user/login');
                }

                // 自动加入平台管理组
                $access['uid'] = $uid;
                $access['group_id'] = 1; // TODO 后续可优化为自动获取
                M('auth_group_access')->insert($access);

                // M( 'invite_code' )->where( wp_where($map) )->delete();//20161213oy
                $userInfo = getUserInfo($uid);
                if ($userInfo['come_from'] == 0 && $userInfo['is_audit'] == 0) {
                    return $this->success('注册成功！', $url);
                }

                D('common/User')->autoLogin($userInfo);
                return $this->success('注册成功！', $url);
            } else {
                // 注册失败，显示错误信息
                return $this->error($this->showRegError($uid));
            }
        } else {
            // 显示注册表单
            return $this->fetch();
        }
    }

    /* 登录页面 */
    public function login($username = '', $password = '', $verify = '', $type = 1)
    {
        if (isset($_GET['sub_redirect_url']) && !empty($_GET['sub_redirect_url'])) {
            //子项目登录回调链接
            cookie('sub_redirect_url', $_GET['sub_redirect_url']);
        }
        // dump(config('tpl_replace_string'));
        $key = cookie('ScanLoginKey');

        if (request()->isPost()) {
            $url = cookie('sub_redirect_url');
            if (input('?post.username')) {
                /* 检测验证码 */
                if (config('app.WEB_SITE_VERIFY') && !captcha_check($verify)) {
                    return $this->error('验证码输入错误！');
                }

                $dao = D('common/User');
                $uid = $dao->login($username, $password, $type);
                if (!$uid) {
                    // 登录失败
                    return $this->error('登录失败');
                }
                $user = getUserInfo($uid);

                $remember = I('remember');
                if ($remember == 1) {
                    cookie('user_id', think_encrypt($uid), 31536000); // 缓存365天
                } else {
                    cookie('user_id', null); // 缓存365天
                }

                if ($url) {
                    //跳转到子项目登录回调链接
                    cookie('sub_redirect_url', null);
                } else {

                    if (config('app.IS_QRCODE_LOGIN') && !$this->isBind($uid)) {
                        $url = U('home/index/bind');
                    } else {
                        $url = U('home/index/main');
                    }
                }

                return $this->success('登录成功！', $url);
            } else {
                $uid = S($key);

                if ($uid > 0) {
                    S($key, null);

                    if ($url) {
                        $url .= '&sub_redirect_key=' . think_encrypt($uid);
                        //跳转到子项目登录回调链接
                        cookie('sub_redirect_url', null);
                    } else {
                        $url = U('admin/apps/index');
                    }
                    $user = getUserInfo($uid);
                    D('common/User')->autoLogin($user);
                    $return['status'] = 1;
                    $return['url'] = $url;
                    $this->ajaxReturn($return, 'JSON');
                } else {
                    echo 0;
                }
            }
        } else {
            if (isMobile()) {
                // 跳转到手机版的个人空间
                return redirect(U('weixin/Wap/userCenter', array(
                    'from' => 1
                )));
            }
            if (is_login()) {
                $url = U('home/index/main');
                return redirect($url);
            }
            if (config('app.IS_QRCODE_LOGIN')) {
                if (empty($key)) {
                    $key = uniqid();
                    cookie('ScanLoginKey', $key);
                }

                $this->assign('key', $key);

                $map['addon'] = 'ScanLogin';
                $map['extra_text'] = $key;
                $info = M('qr_code')->where(wp_where($map))->field(true)->find();
                if ($info && (NOW_TIME - $info['cTime'] > $info['expire_seconds'])) {
                    M('qr_code')->where(wp_where($map))->delete();
                    $info['qr_code'] = '';
                }
                if (!$info['qr_code']) {
                    $info['qr_code'] = D('home/QrCode')->addQrCode('QR_SCENE', 'ScanLogin', 0, 0, $key);
                }
                $this->assign('qrcode', $info['qr_code']);
            }

            $html = 'login';
            isset($_GET['from']) && $_GET['from'] == 'store' && $html = 'simple_login';

            return $this->fetch($html);
        }
    }

    // 判断运营人员是否绑定微信
    private function isBind($uid)
    {
        $openid = M('user')->where('uid', $uid)->value('bind_openid');
        return $openid != '' ? true : false;
    }

    public function bind_login()
    {
        $key = cookie('ScanLoginKey');
        if (request()->isPost()) {
            $has_bind = S($key);
            if ($has_bind == 1) {
                echo 1;
            } else {
                echo 0;
            }
        } else {
            if (empty($key)) {
                $key = uniqid();
                cookie('ScanLoginKey', $key);
            }
            S($key, 0);
            $this->assign('key', $key);

            $map['addon'] = 'ScanBindLogin';
            $map['extra_text'] = $key;
            $info = M('qr_code')->where(wp_where($map))
                ->field(true)
                ->find();

            if ($info && (NOW_TIME - $info['cTime'] > $info['expire_seconds'])) {
                M('qr_code')->where(wp_where($map))->delete();
                $info['qr_code'] = '';
            }
            if (!$info['qr_code']) {
                $info['qr_code'] = D('home/QrCode')->addQrCode('QR_SCENE', 'ScanBindLogin', 0, $this->mid, $key);
            }
            $this->assign('qrcode', $info['qr_code']);

            return $this->fetch();
        }
    }

    /* 退出登录 */
    public function logout()
    {
        $param = [];
        if (isset($_GET['sub_redirect_url']) && !empty($_GET['sub_redirect_url'])) {
            //子项目登录回调链接
            $param['sub_redirect_url'] = $_GET['sub_redirect_url'];
        }

        if (is_login()) {
            $key = cookie('ScanLoginKey');
            S($key, null);

            D('common/User')->logout();

        }
        return redirect(U('User/login') . '&' . http_build_query($param));
    }

    /* 验证码，用于登录和注册 */
    public function verify()
    {
        $captcha = new Captcha();
        return $captcha->entry();
    }

    /**
     * 获取用户注册错误信息
     *
     * @param integer $code
     *            错误编码
     * @return string 错误信息
     */
    private function showRegError($code = 0)
    {
        switch ($code) {
            case -1:
                $error = '用户名长度必须在16个字符以内！';
                break;
            case -2:
                $error = '用户名被禁止注册！';
                break;
            case -3:
                $error = '用户名被占用！';
                break;
            case -4:
                $error = '密码长度必须在6-30个字符之间！';
                break;
            case -5:
                $error = '邮箱格式不正确！';
                break;
            case -6:
                $error = '邮箱长度必须在1-32个字符之间！';
                break;
            case -7:
                $error = '邮箱被禁止注册！';
                break;
            case -8:
                $error = '邮箱被占用！';
                break;
            case -9:
                $error = '手机格式不正确！';
                break;
            case -10:
                $error = '手机被禁止注册！';
                break;
            case -11:
                $error = '手机号被占用！';
                break;
            default:
                // $error = '未知错误';
                $error = '用户名被占用！';
        }
        return $error;
    }

    /**
     * 修改密码提交
     *
     * @author huajie <banhuajie@163.com>
     */
    public function profile()
    {
        if (!is_login()) {
            return $this->error('您还没有登陆', U('home/User/login'));
        }
        if (request()->isPost()) {
            // 获取参数
            $uid = is_login();
            $password = I('post.old');
            $repassword = I('post.repassword');
            $data['password'] = I('post.password');
            if (empty($password)) return $this->error('请输入原密码');
            if (empty($data['password'])) return $this->error('请输入新密码');
            if (empty($repassword)) return $this->error('请输入确认密码');

            if ($data['password'] !== $repassword) {
                return $this->error('您输入的新密码与确认密码不一致');
            }
            $data['login_password'] = $data['password'];
            $res = D('common/User')->updateUserFields($uid, $password, $data);
            if ($res !== false) {
                return $this->success('修改密码成功！');
            } else {
                return $this->error('修改密码失败！');
            }
        } else {
            return $this->fetch();
        }
    }

    function edit()
    {
        $model = $this->getModel('user');
        $id = $this->mid;

        $data = M($model['name'])->where('uid', $id)->find();

        if (request()->isPost()) {
            $Model = D($model['name']);
            // 获取模型的字段信息
            $post = empty($post_data) ? input('post.') : $post_data;
            $post = $this->checkData($post, $model);

            $res = $Model->where('uid', $id)->update($post);


            if ($res !== false) {
                // 清空缓存
                method_exists($Model, 'clearCache') && $Model->clearCache($id, 'edit');

                return $this->success('保存' . $model['title'] . '成功！');
            } else {
                return $this->error($Model->getError());
            }
        } else {
            $fields = get_model_attribute($model);
            $this->assign('fields', $fields);
            empty($data) && $data = default_form_value($fields);
            $this->assign('data', $data);

            empty($templateFile) && $templateFile = 'edit';

            return $this->fetch($templateFile);
        }
    }

    function getSession()
    {
        return json(session());
    }
}
