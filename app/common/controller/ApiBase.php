<?php

// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星 <xw@bctos.cn> <QQ:203163051>
// +----------------------------------------------------------------------
namespace app\common\controller;

use app\common\controller\Base;

/**
 * 手机H5版的控制器基类，实现手机端的初始化，权限控制和一些通用方法
 *
 * @author 凡星
 *
 */
class ApiBase extends Base
{
    protected $check_sign = false; //是否需要验证签名

    var $check_login = true;//是否需要登录
    var $token = '';

    public function initialize()
    {
        parent::initialize();

        $this->log();

        if (MODULE_NAME == 'weiapp' || CONTROLLER_NAME == 'ApiBase' || ACTION_NAME == 'login' || input('?no_login')) {
            $this->check_login = false;
        }
        try {
            $this->checkToken();

            if ($this->check_sign) {
                $this->checkSign();
            }
        } catch (\Exception $e) {
            // 这是进行异常捕获
            echo json($this->apiError($e->getMessage()));
            exit;
        }
    }

    //记录API日志，方便后续的API报表统计
    function log()
    {
        $param = input('');
        $log['url'] = MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME;
        $log['param'] = is_array($param) ? json_encode($param) : $param;
        $log['server_ip'] = get_client_ip();
        $log['create_at'] = NOW_TIME;
        M('api_log')->insert($log);

        // 自动删除48小时以前正常的日志
        $key = 'clean_api_log_' . date('Ymd');
        $day_lock = S($key);
        if ($day_lock !== false) {
            return true; // 一天只清一次
        } else {
            S($key, 1, 86400);
        }

        $map['create_at'] = [
            '<',
            NOW_TIME - 172800
        ];
        M('api_log')->where(wp_where($map))->delete();
    }

    private function checkToken(): void
    {
        $token = input('token');
        if (empty ($token)) {
            $rand = rand(100, 999);
            $token = md5(NOW_TIME . $rand);
            S('check_' . $token, -1);
        }

        // 优先从缓存中查检
        $uid = S('check_' . $token);
        if ($uid === false) {
            throw new \think\Exception('token不正确', 115004);
        }

        $this->token = $token;
        if ($uid == -1) {
            //用户还登录，还没绑定用户信息
            $GLOBALS ['mid'] = $this->mid = 0;
            $GLOBALS ['myinfo'] = [];
            if ($this->check_login) {
                $this->apiError('需要登录', [], 10);
            }
        } else {
            // 当前登录者
            $GLOBALS ['mid'] = $this->mid = $uid;
            $GLOBALS ['myinfo'] = get_userinfo($uid);
        }
    }

    private function checkSign(): void
    {
        if (IS_POST) {
            $data = input('post.');
        } else {
            $data = input('get.');
        }
        if (!isset($data['sign'])) {
            throw new \think\Exception('sign签名参数不存在');
        }

        $sign = $data['sign'];
        unset($data['sign']);

        ksort($data);
        $str = http_build_query($data);
        $check_sign = strtolower(md5($str));
        if ($check_sign != $sign) {
            throw new \think\Exception('签名不正确');
        }
    }

    function merge()
    {
        $apis = input('apis');
        $apis = json_decode($apis, true);
        $res = [];
        foreach ($apis as $api => $param) {
            list($module, $controller, $actor) = explode('/', $api, 3);
            $res[$module . '_' . $controller . '_' . $actor] = controller($module . '/' . $controller)->$actor($param);
        }
        return $this->apiSuccess($res);
    }

    public function apiError($msg = '操作失败', $data = [], $code = 1)
    {
        return $this->result($data, $code, $msg);
    }

    public function apiSuccess($data = [], $msg = '操作成功')
    {
        return $this->result($data, 0, $msg);
    }

    /**
     * 返回封装后的API数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param integer $code 返回的code
     * @param mixed $msg 提示信息
     * @return void
     */
    protected function result($data, $code = 0, $msg = '')
    {
        $result = [
            'code' => $code,
            'msg' => $msg,
            'server_time' => NOW_TIME,
            'data' => $data,
            'mid' => isset($GLOBALS['mid']) ? $GLOBALS['mid'] : 0,
            'myinfo' => isset($GLOBALS['myinfo']) ? $GLOBALS['myinfo'] : [],
            'wpid' => get_wpid()
        ];

        if (!isset($data['share'])) {
            $imageUrl = '';
            $result['share'] = ['title' => '看直播领现金，直播打卡挑战等你来！', 'path' => '/pages/index/index?from_uid=' . $result['mid'], 'imageUrl' => $imageUrl];
        }

        if (isset($GLOBALS['extend_data'])) {
            $result = array_merge($result, $GLOBALS['extend_data']);
        }
        return $this->ajaxReturn($result);
    }

    public function _empty($name)
    {
        $mid = $GLOBALS['mid'];

        try {
            $this->apiModel->setMid($mid);
            $data = $this->apiModel->$name ();
        } catch (\Exception $e) {
            // 这是进行异常捕获
            return $this->apiError($e->getMessage());
        }

        if (is_array($data)) {
            $data['mid'] = $mid;
            $data['server_time'] = time();
            $data['wpid'] = get_wpid();
            $data['myinfo'] = $GLOBALS['myinfo'];
            if (!isset($data['share'])) {
                $imageUrl = '';
                $data['share'] = ['title' => '看直播领现金，直播打卡挑战等你来！', 'path' => '/pages/index/index?from_uid=' . $mid, 'imageUrl' => $imageUrl];
            }

            if (isset($GLOBALS['extend_data'])) {
                $data = array_merge($data, $GLOBALS['extend_data']);
            }
        }
        $debug = input('debug');
        if ($debug == 1) {
            dump($data);
        }
        return is_numeric($data) || is_string($data) ? $data : json($data);
    }

    function add()
    {
        $model = I('model');
        $model = $this->getModel($model);

        return $this->apiSuccess(parent::commonAdd($model));
    }

    function edit()
    {
        $model = I('model');
        $model = $this->getModel($model);

        return $this->apiSuccess(parent::commonEdit($model));
    }
}