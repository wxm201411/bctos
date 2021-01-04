<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星 <xw@bctos.cn> <QQ:203163051>
// +----------------------------------------------------------------------
namespace app\common\controller;

use app\BaseController;
use app\card\controller\Api;
use think\Validate;
use think\facade\Log;
use think\facade\View;
use think\facade\Env;
use think\Response;

/**
 * 小韦云的系统的核心基类，包括手机版和PC版，主要实现系统级别的初始化和一些通用方法
 *
 * @author Administrator
 *
 */
class Base extends BaseController
{
    protected $mid = 0;

    protected $uid = 0;

    protected $user = [];

    protected $get_param = [];

    public function __call($name, $arguments)
    {
        $act = request()->action(true);
        if (0 === strcasecmp($name, $act)) {
            if (method_exists($this, '_empty')) {
                return $this->_empty($act);
            }
        }
    }

    public function __construct()
    {
        //开启调试模式下自动清空opcache缓存
        if (config('app.app_debug') && function_exists('opcache_reset')) {
            opcache_reset();
        }

        // 小韦云常量定义
        defined('MODULE_NAME') or define('MODULE_NAME', strtolower(app('http')->getName()));
        defined('CONTROLLER_NAME') or define('CONTROLLER_NAME', request()->controller());
        defined('ACTION_NAME') or define('ACTION_NAME', request()->action(true));

        defined('ADDON_PUBLIC_PATH') or define('ADDON_PUBLIC_PATH', __ROOT__ . '/' . MODULE_NAME . '');

        defined('NOW_TIME') or define('NOW_TIME', $_SERVER['REQUEST_TIME']);
        defined('IS_GET') or define('IS_GET', request()->isGet());
        defined('IS_POST') or define('IS_POST', request()->isPost());
        defined('IS_AJAX') or define('IS_AJAX', request()->isAjax() || CONTROLLER_NAME == 'Api');

        $url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        defined('__SELF__') or define('__SELF__', strip_tags($url));
        if (Env::get('APP_DEBUG')) {
            defined('SITE_VERSION') or define('SITE_VERSION', time());
        } else {
            defined('SITE_VERSION') or define('SITE_VERSION', 1);
        }

        $requestData = input();
        $requestData = empty($requestData) ? [] : $requestData;
        $_REQUEST = array_merge($_REQUEST, $requestData);

        // 不用记录定时任务的日志
        if (ACTION_NAME != 'cron' && CONTROLLER_NAME != 'Canal' && CONTROLLER_NAME != 'Cron') {
            config(['close' => false], 'log');
        } else {
            config(['close' => true], 'log');
        }
        // 解决TP框架中的GET不包含PHP_INFO里的参数的问题
        $route = input('route.');
        $_GET = array_merge($route, $_GET);

        $pbid = input('pbid/d', 0);
        if ($pbid > 0) {
            session('pbid', $pbid);
        }
        if ($pbid == 0) {
            $pbid = intval(session('pbid'));
        }
        if ($pbid == 0 && DEFAULT_PBID != '-1') {
            $pbid = DEFAULT_PBID;
        }

        if ($pbid == 0 && strtolower(MODULE_NAME) != 'install') {
            // 在单账号系统中，没有指定公众号的情况下取第一个
            $pbid = D('common/Publics')->value('id');
        }

        $wpid = session('wpid');
        if (!$wpid && strtolower(MODULE_NAME) != 'install') {
            //$wpid = session('mid'); // 管理后台的wpid直接取mid，后续可优化
            //session('wpid', $wpid);
        }

        if (!$wpid && DEFAULT_WPID != '-1') {
            $wpid = DEFAULT_WPID;
        }

        if (!defined('PBID')) { //&& strtolower(MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME) != 'home/weixin/index'
            defined('PBID') || define('PBID', $pbid);
            defined('WPID') || define('WPID', $wpid);
        }

        $public_dir = parse_name(MODULE_NAME);
        if (!is_dir(SITE_PATH . '/public/' . $public_dir)) {
            $public_dir = 'home';
        }
        $theme = get_theme($public_dir);
        if ($theme) {
            $public_dir .= '/' . $theme;
        }
        config(['tpl_replace_string' => array(
            '__STATIC__' => __ROOT__ . '/static',
            '_MODULE_NAME__' => __ROOT__ . '/' . $public_dir . '/addons',
            '__IMG__' => __ROOT__ . '/' . $public_dir . '/images',
            '__CSS__' => __ROOT__ . '/' . $public_dir . '/css',
            '__JS__' => __ROOT__ . '/' . $public_dir . '/js',
            '__ROOT__' => __ROOT__, // 当前网站地址
            '__SELF__' => __SELF__, // 当前页面地址
            '__PUBLIC__' => __ROOT__,
            '__PUBLICTHIS__' => __ROOT__ . '/' . $public_dir
        )], 'view'); // 站点公共目录

        // 控制器初始化
        $this->initialize();
    }

    function initialize()
    {
        if (strtolower(MODULE_NAME) != 'install') {
            $this->initSite();
        }
    }

    /**
     * 应用信息初始化
     *
     * @access private
     * @return void
     */
    private function initSite()
    {
        //设置自动检测
        if (input('?auto_check')) {
            session('auto_check', 1);
        } elseif (input('?auto_check_del')) {
            session('auto_check', 0);
        }
        $auto_check_open = intval(session('auto_check'));
        $this->assign('auto_check_open', $auto_check_open);

        //判断当前应用是否被禁用
        $key = cache_key(['name' => MODULE_NAME], 'apps', 'id,status');
        $app = S($key);
        if ($app === false) {
            $app = M('apps')->where('name', MODULE_NAME)->field('id,status,name')->find();
            S($key, $app);
        }
        if (isset($app['status']) && $app['status'] == 0) {
            return $this->error('该应用已被禁用');
        }

        /* 读取数据库中的配置 */
        $config = S('DB_CONFIG_DATA');
        if (!$config) {
            $config = api('Config/lists');
            S('DB_CONFIG_DATA', $config);
        }
        config($config, 'app');

        $diff = array(
            '_addons' => 1,
            '_controller' => 1,
            '_action' => 1,
            'm' => 1,
            'id' => 1
        );

        $GLOBALS['get_param'] = $this->get_param = array_diff_key($_GET, $diff);
        if (isset($this->get_param['page'])) {
            unset($this->get_param['page']);
        }
        $this->assign('get_param', $this->get_param);

        // js,css的版本
        if (Env::get('APP_DEBUG')) {
            defined('SITE_VERSION') or define('SITE_VERSION', time());
        } else {
            defined('SITE_VERSION') or define('SITE_VERSION', config('app.SYSTEM_UPDATRE_VERSION'));
        }
        // 公众号信息
        $info = $public_info = get_pbid_appinfo();
        $this->assign('public_info', $public_info);

        //$page_title = isset($info['public_name']) && CONTROLLER_NAME == 'Wap' ? $info['public_name'] : $config['WEB_SITE_TITLE'];
        //$this->assign('page_title', $page_title);
        $this->assign('page_title', $config['WEB_SITE_TITLE']);

        // 设置版权信息
        $this->assign('system_copy_right', config('app.COPYRIGHT'));

        $tongji_code = '';
        $this->assign('tongji_code', $tongji_code);

        if (MODULE_NAME == 'scene') {
            error_reporting(E_ERROR | E_PARSE);
        }

        //判断是否需要获取软件更新信息
        $need_check_update = 0;
        $key = "need_check_update_" . date('Ymd');
        $lock = S($key);
        if ($lock === false && CONTROLLER_NAME != 'Update') {
            $need_check_update = 1;
            S($key, 1, 86400); //每天只检查一次
        }
        $this->assign('need_check_update', $need_check_update);

        //其它一些社区初始化变量
        $this->assign('initNums', 0);
        $this->assign('attachConfig', ['attach_max_size' => 2]);
        $this->assign('imageConfig', ['attach_max_size' => 2]);
        $this->assign('social_id', 1);
        $this->assign('UploadFileExts', '');
        $this->assign('unsetting', '');
        $this->assign('is_post_detail', 0);
    }

    // ***************************通用的模型数据操作 begin 凡星********************************/
    public function getModel($model = null)
    {
        $model = get_model($model);
        $this->assign('model', $model);
        return $model;
    }

    /**
     * 显示指定模型列表数据
     *
     * @param String $model
     *            模型标识
     * @author 凡星
     */
    public function commonLists($model = null, $templateFile = '', $order = 'id desc')
    {
        // 获取模型信息
        is_array($model) || $model = $this->getModel($model);
        $list_data = $this->getModelList($model, $order);
        $this->assign($list_data);

        empty($templateFile) && $templateFile = 'lists';

        return $this->fetch($templateFile);
    }

    // 只返回列表数据，方便作后续业务处理
    public function commonListsData($model = null, $templateFile = '', $order = 'id desc')
    {
        // 获取模型信息
        is_array($model) || $model = $this->getModel($model);
        $list_data = $this->getModelList($model, $order);

        return $list_data;
    }

    public function commonExport($model = null, $order = 'id desc', $return = false)
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(0);
        }
        // 获取模型信息
        is_array($model) || $model = $this->getModel($model);
        // 解析列表规则
        $list_data = $this->listGrid($model);
        $grids = $list_data['list_grids'];
        $fields = $list_data['fields'];

        foreach ($grids as $k => $v) {
            if ($v['come_from'] == 1) {
                array_pop($grids);
            } else {
                $ht[$k] = $v['title'];
            }
        }
        $dataArr[0] = $ht;

        // 搜索条件
        $map = $this->searchMap($model, $list_data['db_fields']);

        $name = parse_name($model['name'], 1);
        $data = M($name)->field(empty($fields) ? true : $fields)
            ->where(wp_where($map))
            ->order($order)
            ->select();

        if ($data) {
            $dataTable = D('common/Models')->getFileInfo($model);
            $data = $this->parseListData($data, $dataTable);
            foreach ($data as &$vo) {
                foreach ($ht as $key => $val) {
                    $newArr[$key] = empty($vo[$key]) ? ' ' : $vo[$key];
                }
                $vo = $newArr;
            }

            $dataArr = array_merge($dataArr, $data);
        }

        if ($return) {
            return $dataArr;
        } else {
            outExcel($dataArr);
        }
    }

    public function commonDel($model = null, $ids = null)
    {
        is_array($model) || $model = $this->getModel($model);

        !empty($ids) || $ids = I('id');
        !empty($ids) || $ids = array_filter(array_unique((array)I('ids', 0)));
        if (empty($ids)) return $this->error('请选择要操作的数据!');

        try {
            $Model = D($model['name']);
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'not exists') || strpos($e->getMessage(), '不存在')) {
                $Model = M($model['name']);
            } else {
                return $this->error('找不到操作模型');
            }
        }
        $map[] = array(
            'id',
            'in',
            $ids
        );

        // 插件里的操作自动加上Token限制
        $dataTable = D('common/Models')->getFileInfo($model);
        $wpid = get_wpid();
        if (!empty($wpid) && isset($dataTable->fields['wpid'])) {
            $map[] = [
                'wpid',
                '=',
                $wpid
            ];
        }

        if ($Model->where(wp_where($map))->delete()) {
            // 清空缓存
            method_exists($Model, 'clearCache') && $Model->clearCache($ids, 'del');

            return $this->success('删除成功');
        } else {
            return $this->error('删除失败！');
        }
    }

    public function commonEdit($model = null, $id = 0, $templateFile = '', $post_data = [])
    {
        is_array($model) || $model = $this->getModel($model);
        $id || $id = id();

        if ($id > 0) {
            // 获取数据
            $data = M($model['name'])->where('id', $id)->find();
            if (empty($data)) return $this->error('数据不存在！');

            $wpid = get_wpid();
            if (isset($data['wpid']) && $wpid != $data['wpid']) {
                return $this->error('非法访问！');
            }
        }

        if (request()->isPost()) {
            try {
                $Model = D($model['name']);
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'not exists') || strpos($e->getMessage(), '不存在')) {
                    $Model = M($model['name']);
                } else {
                    return $this->error('找不到操作模型');
                }
            }
            // 获取模型的字段信息
            $post = empty($post_data) ? input('post.') : $post_data;
            $post = $this->checkData($post, $model);
            if ($id > 0) {
                $res = $Model->where('id', $id)->update($post);
            } else {
                if (isset($post['id'])) unset($post['id']);
                $res = $id = $Model->insertGetId($post);
            }

            if ($res !== false) {
                $this->_saveKeyword($model, $id);

                // 清空缓存
                method_exists($Model, 'clearCache') && $Model->clearCache($id, 'edit');

                return $this->success('保存' . $model['title'] . '成功！', U('lists?model=' . $model['name'], $this->get_param));
            } else {
                return $this->error($Model->getError());
            }
        } else {
            $fields = get_model_attribute($model);
            empty($data) && $data = default_form_value($fields);
            $post_url = U('edit?model=' . $model['id'], $GLOBALS['get_param']);

            if (CONTROLLER_NAME == 'Api') {
                $dataTable = D('common/Models')->getFileInfo($model);
                return ['fields' => $this->filterApiField($fields), 'data' => $this->parseModelData($data, $dataTable), 'post_url' => $post_url];
            } else {
                $this->assign('fields', $fields);
                $this->assign('data', $data);
                $this->assign('post_url', $post_url);

                empty($templateFile) && $templateFile = 'edit';
                return $this->fetch($templateFile);
            }
        }
    }

    public function commonAdd($model = null, $templateFile = '', $post_data = [])
    {
        is_array($model) || $model = $this->getModel($model);
        if (request()->isPost()) {
            try {
                $Model = D($model['name']);
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'not exists') || strpos($e->getMessage(), '不存在')) {
                    $Model = M($model['name']);
                } else {
                    return $this->error('找不到操作模型');
                }
            }
            // 获取模型的字段信息
            $data = empty($post_data) ? input('post.') : $post_data;
            $data = $this->checkData($data, $model);
            // dump($data);exit;
            $id = $Model->insertGetId($data);
            if ($id) {
                $this->_saveKeyword($model, $id);

                // 清空缓存
                method_exists($Model, 'clearCache') && $Model->clearCache($id, 'add');

                return $this->success('添加' . $model['title'] . '成功！', U('lists?model=' . $model['name'], $this->get_param));
            } else {
                return $this->error($Model->getError());
            }
        } else {
            $fields = get_model_attribute($model);
            $data = default_form_value($fields);
            $post_url = U('add?model=' . $model['id'], $GLOBALS['get_param']);

            if (CONTROLLER_NAME == 'Api') {
                return ['fields' => $this->filterApiField($fields), 'data' => $data, 'post_url' => $post_url];
            } else {
                $this->assign('fields', $fields);
                $this->assign('data', $data);
                $this->assign('post_url', $post_url);
                // dump($fields);
                empty($templateFile) && $templateFile = 'add';

                return $this->fetch($templateFile);
            }
        }
    }

    private function filterApiField($fields)
    {
        $allow = ['num' => 1, 'textarea' => 1, 'editor' => 1, 'datetime' => 1, 'date' => 1, 'bool' => 1, 'radio' => 1, 'checkbox' => 1, 'dynamic_checkbox' => 1, 'select' => 1,
            'dynamic_select' => 1, 'picture' => 1, 'mult_picture' => 1, 'string' => 1];

        foreach ($fields as $k => $f) {
            if (!isset($allow[$f['type']])) {
                unset($fields[$k]);
            }
        }
        return $fields;
    }

// 判断奖品库选择器 数量是否大于库存
    public function checkPriceNum($prizeValue)
    {
        $data = [];
        $prizeData = explode(',', $prizeValue);
        foreach ($prizeData as $key => $value) {
            $keyArr = explode(':', $value);
            if (empty($keyArr[0])) {
                continue;
            }

            $total_count = 0;
            $num = $keyArr[2];
            $title = '';
            $pdata = [];
            if ($keyArr[0] == 'coupon') {
                $pdata = D('Coupon/Coupon')->getInfo($keyArr[1]);
                $title = $pdata['title'];
                $total_count = $pdata['num'];
            } elseif ($keyArr[0] == 'realPrize') {
                $pdata = D('RealPrize/RealPrize')->getInfo($keyArr[1]);
                $total_count = $pdata['prize_count'];
                $title = $pdata['prize_name'];
            } elseif ($keyArr[0] == 'cardVouchers') {
                // 无库存，不判断
                $title = $pdata['title'];
                if (intval($num) <= 0) {
                    return $this->error('奖品 “' . $title . '” 的数量不能小于0');
                }
                continue;
            } elseif ($keyArr[0] == 'redBag') {
                $pdata = D('Redbag/Redbag')->getInfo($keyArr[1]);
                $title = $pdata['act_name'];
                $total_count = $pdata['total_num'];
            } elseif ($keyArr[0] == 'points') {
                // 判断数量是否小于0
                $title = '积分';
                $num = $keyArr[3];
                if (intval($num) <= 0) {
                    return $this->error('奖品 “' . $title . '” 的数量不能小于0');
                }
                continue;
            }
            if (intval($num) <= 0) {
                return $this->error('奖品 “' . $title . '” 的数量不能小于0');
            }
            if ($num > $total_count) {
                return $this->error('奖品 “' . $title . '” 的数量不能大于库存数量');
            }
        }
    }

    protected function checkData($data, $model = [])
    {
        $fields = get_model_attribute($model);
        $fields = isset($fields) ? $fields : [];
        $rules = $message = [];
        foreach ($fields as $key => $attr) {
            if ($attr['type'] == 'prize' && input('post.' . $key)) {
                // 判断奖品库选择器 数量是否大于库存
                $this->checkPriceNum(input('post.' . $key));
            }
            if ($attr['is_must']) {
                // 必填字段
                $rules[$attr['name']] = 'require';
                $message[$attr['name'] . '.require'] = $attr['title'] . '必须!';
            }
            // 自动验证规则
            if (!empty($attr['validate_rule']) || $attr['validate_type'] == 'unique') {
                switch ($attr['validate_type']) {
                    case 'function': // 函数验证
                        $rules[$attr['name']] = 'filter' . $attr['validate_rule'];
                        $message[$attr['name'] . '.filter'] = $attr['error_info'] ? $attr['error_info'] : $attr['title'] . '验证错误';
                        break;
                    case 'unique': // 唯一验证
                        $rules[$attr['name']] = 'unique:' . $attr['validate_rule'];
                        $message[$attr['name'] . '.unique'] = $attr['error_info'] ? $attr['error_info'] : $attr['title'] . '验证错误';
                        break;
                    case 'length': // 长度验证
                        $rules[$attr['name']] = 'length:' . $attr['validate_rule'];
                        $message[$attr['name'] . '.length'] = $attr['error_info'] ? $attr['error_info'] : $attr['title'] . '验证错误';
                        break;
                    case 'in': // 验证在范围内
                        $rules[$attr['name']] = 'in:' . $attr['validate_rule'];
                        $message[$attr['name'] . '.in'] = $attr['error_info'] ? $attr['error_info'] : $attr['title'] . '验证错误';
                        break;
                    case 'notin': // 验证不在范围内
                        $rules[$attr['name']] = 'notIn:' . $attr['validate_rule'];
                        $message[$attr['name'] . '.notIn'] = $attr['error_info'] ? $attr['error_info'] : $attr['title'] . '验证错误';
                        break;
                    case 'between': // 区间验证
                        $rules[$attr['name']] = 'between:' . $attr['validate_rule'];
                        $message[$attr['name'] . '.between'] = $attr['error_info'] ? $attr['error_info'] : $attr['title'] . '验证错误';
                        break;
                    case 'notbetween': // 不在区间验证
                        $rules[$attr['name']] = 'notBetween:' . $attr['validate_rule'];
                        $message[$attr['name'] . '.notBetween'] = $attr['error_info'] ? $attr['error_info'] : $attr['title'] . '验证错误';
                    default: // 正则验证
                        $rules[$attr['name']] = 'regex:' . $attr['validate_rule'];
                        $message[$attr['name'] . '.regex'] = $attr['error_info'] ? $attr['error_info'] : $attr['title'] . '验证错误';
                }
            }

            // 自动完成规则
            if (!empty($attr['auto_rule'])) {
                $check_model = check_model($model['name']);
                $pk = $check_model ? D($model['name'])->getPk() : 'id';
                empty($pk) && $pk = 'id';
                $type = isset($data[$pk]) && !empty($data[$pk]) ? MODEL_UPDATE : MODEL_INSERT;

                if (!isset($data[$attr['name']]) && ($attr['auto_time'] == $type || $attr['auto_time'] == MODEL_BOTH)) {
                    switch ($attr['auto_type']) {
                        case 'function':
                            $fun = $attr['auto_rule'];
                            $param = [];
                            if (strpos($attr['auto_rule'], '|') !== false) {
                                list ($fun, $param) = explode('|', $attr['auto_rule']);

                                if (strpos($param, ',') !== false) {
                                    $param = explode(',', $param);
                                }
                            }
                            $data[$attr['name']] = call_user_func_array($fun, $param);
                            break;
                        case 'field':
                            $data[$attr['name']] = "`{$attr['auto_rule']}`";
                            break;
                        case 'string':
                            $data[$attr['name']] = $attr['auto_rule'];
                            break;
                    }
                }
            } elseif ('checkbox' == $attr['type'] || 'dynamic_checkbox' == $attr['type']) {
                // 多选型
                if (isset($data[$attr['name']])) {
                    $data[$attr['name']] = arr2str($data[$attr['name']]);
                }
            } elseif ('datetime' == $attr['type'] || 'date' == $attr['type']) {
                // 时间或者日期型
                if (isset($data[$attr['name']]) && !empty($data[$attr['name']])) {
                    //没有的不设置为空，以免覆盖新增保存的数据
                    $data[$attr['name']] = strtotime($data[$attr['name']]);
                }

            } elseif ('mult_picture' == $attr['type']) {
                // 多图
                if (isset($data[$attr['name']])) {
                    $data[$attr['name']] = $data[$attr['name']];
                    if (is_array($data[$attr['name']])) {
                        $data[$attr['name']] = arr2str($data[$attr['name']]);
                    }
                }
            } elseif ('cascade' == $attr['type']) {
                // 级联，而且字段类型为数字时只取最后一级的ID值
                if (isset($data[$attr['name']]) && stripos($attr['field'], 'int') !== false) {
                    $pids = array_filter(explode(',', $data[$attr['name']]));
                    $data[$attr['name']] = intval(array_pop($pids));
                }
            }

        }
        if (!empty($rules)) {
            $validate = \think\facade\Validate::rule($rules)->message($message);
            if (!$validate->check($data)) {
                return $this->error($validate->getError());
            }
        }
        if (isset($data['start_time']) && isset($data['end_time'])) {
            if ($data['end_time'] <= $data['start_time']) {
                return $this->error('结束时间不能早于开始时间');
            }
        }
        return $data;
    }

    protected function parseDataByField($val, $field, $id = 0, $model = [], $grid = [])
    {
        $has_chang = false;
        if (empty($field)) {
            return ['value' => $val, 'has_chang' => $has_chang];
        }

        switch ($field['type']) {
            case 'date':
                $val = day_format($val);
                $has_chang = true;
                break;
            case 'datetime':
                $val = time_format($val);
                $has_chang = true;
                break;
            case 'bool':
                $url = U('switchAjaxUpdate', ['table' => $model['name'], 'field' => $field['name'], 'id' => $id]);
                $checked = $val ? 'checked' : '';
                $extra = parse_field_attr($field['extra']);
                if (isset($GLOBALS['forbit_audit']) || $grid['can_edit'] == 0) {
                    $val = isset($extra[$val]) ? $extra[$val] : $val;
                } elseif (count($extra) == 2) {
                    isset($extra[0]) || $extra[0] = '否';
                    isset($extra[1]) || $extra[1] = '是';

                    $text = $extra[1] . '|' . $extra[0];

                    $val = '<span class="layui-form"><input url="' . $url . '" ' . $checked . ' type="checkbox" lay-skin="switch" lay-text="' . $text . '" lay-filter="switch-ajax-update" /></span>';
                } else {
                    $remark_url = U('switchAjaxUpdate', ['table' => $model['name'], 'field' => 'remark', 'id' => $id]);
                    $html = '<span class="layui-form lists-select"><select url="' . $url . '" remark_url="' . $remark_url . '"  lay-filter="switch-ajax-update">';

                    foreach ($extra as $k => $v) {
                        if ($val == $k) {
                            $html .= '<option value="' . $k . '" selected>' . $v . '</option>';
                        } else {
                            $html .= '<option value="' . $k . '">' . $v . '</option>';
                        }
                    }
                    $html .= '</select></span> ';
                    $val = $html;
                }
                $has_chang = true;

                break;
            case 'select':
            case 'radio':
                if (!empty($field['extra'])) {
                    $extra = parse_field_attr($field['extra']);
                    $val = isset($extra[$val]) ? $extra[$val] : $val;
                }
                $has_chang = true;

                break;
            case 'checkbox':
                if (!empty($field['extra'])) {
                    $extra = parse_field_attr($field['extra']);

                    $valArr = explode(',', $val);
                    foreach ($valArr as $v) {
                        $res[] = isset($extra[$v]) ? $extra[$v] : $v;
                    }

                    $val = implode(', ', $res);
                }
                $has_chang = true;
                break;
            case 'icon':
                if ($val) {
                    $val = '<i class="' . $val . '" style="font-size: 45px;"></i>';
                    $has_chang = true;
                }
                break;
            case 'picture':
                $val = get_img_html($val);
                $has_chang = true;
                break;
            case 'file':
                $val = get_file_html($val);
                $has_chang = true;
                break;

            case 'mult_picture':
                $valArr = explode(',', $val);
                foreach ($valArr as $v) {
                    $res[] = get_img_html($v);
                }

                $val = implode(' ', $res);
                $has_chang = true;
                break;
            case 'dynamic_checkbox':
            case 'cascade':
            case 'dynamic_select':
                $val = $this->dynamicTitle($val, $field);
                $has_chang = true;
                break;

            case 'material':
                $val = W('common/Material/material', array('name' => $field['name'], 'value' => $val));
                break;
            case 'prize':
                break;
            case 'news':
                $val = W('common/Material/material', array('name' => $field['name'], 'value' => 'news:' . $val));
                break;
            case 'admin':
            case 'user':
            case 'users':
                $valArr = explode(',', $val);
                foreach ($valArr as $v) {
                    $res[] = get_nickname($v);
                }
                $val = implode(', ', $res);
                $has_chang = true;
                break;
        }

        return ['value' => $val, 'has_chang' => $has_chang];
    }

    protected function parseDataByFieldByApi($val, $field, $id = 0, $model = [], $grid = [])
    {
        $has_chang = true;
        if (empty($field)) {
            return ['value' => $val, 'has_chang' => $has_chang];
        }

        switch ($field['type']) {
            case 'date':
                $val = day_format($val);
                break;
            case 'datetime':
                $val = date('H:i', $val);
                break;
            case 'bool':
            case 'select':
            case 'radio':
                if (!empty($field['extra'])) {
                    $extra = parse_field_attr($field['extra']);
                    $val = isset($extra[$val]) ? $extra[$val] : $val;
                }
                break;
            case 'checkbox':
                if (!empty($field['extra'])) {
                    $extra = parse_field_attr($field['extra']);

                    $valArr = explode(',', $val);
                    foreach ($valArr as $v) {
                        $res[] = isset($extra[$v]) ? $extra[$v] : $v;
                    }

                    $val = implode(', ', $res);
                }
                break;
            case 'picture':
                $val = [get_cover_url($val)];
                break;
            case 'file':
                $val = [get_file_url($val)];
                break;

            case 'mult_picture':
                $valArr = explode(',', $val);
                foreach ($valArr as $v) {
                    $res[] = get_cover_url($v);
                }

                $val = $res;
                break;
            case 'dynamic_checkbox':
            case 'cascade':
            case 'dynamic_select':
                $val = $this->dynamicTitle($val, $field);
                break;
            case 'admin':
            case 'user':
            case 'users':
                $valArr = explode(',', $val);
                foreach ($valArr as $v) {
                    $res[] = get_nickname($v);
                }
                $val = implode(', ', $res);
                break;
        }

        return ['value' => $val, 'has_chang' => $has_chang];
    }

    protected function dynamicTitle($val, $field)
    {
        if (empty($field['extra']) || empty($val)) {
            return $val;
        }

        parse_str($field['extra'], $arr);
        $table = !empty($arr['table']) ? $arr['table'] : 'common_category'; // 表名
        $value_field = !empty($arr['value_field']) ? $arr['value_field'] : 'id'; // 值对应的字段名
        $title_field = !empty($arr['title_field']) ? $arr['title_field'] : 'title'; // 显示的内容

        $valArr = wp_explode($val);
        // 查询对应选中值对应的显示内容
        $lists = M($table)->whereIn($value_field, $valArr)->column($title_field, $value_field);
        $resArr = [];
        foreach ($valArr as $id) {
            $resArr[] = isset($lists[$id]) ? $lists[$id] : $id;
        }
        return implode(', ', $resArr);
    }

    protected function parsePageData($data, $model, $list_data = [], $assign = true)
    {
        $data = dealPage($data);
        if (!empty($list_data)) {
            $list_data = array_merge($list_data, $data);
        } else {
            $list_data = $data;
        }

        if (!empty($model)) {
            $list_data['list_data'] = $this->parseListData($list_data['list_data'], $model);
        }
        if ($assign) {
            $this->assign($list_data);
        }

        return $list_data;
    }

    protected function parseListData($datas, $mutl)
    {
        if (empty($datas) || empty($mutl)) {
            return [];
        }
        $datas = find_data($datas);

        if (is_array($mutl)) {
            $dataTable = D('common/Models')->getFileInfo($mutl);
        } else {
            $dataTable = $mutl;
        }

        if (empty($dataTable)) {
            return $datas;
        }

        foreach ($datas as $key => &$data) {
            if (empty($data)) {
                unset($datas[$key]);
                continue;
            }
            $data = $this->parseModelData($data, $dataTable);
        }

        return $datas;
    }

    public function parseModelData($data, $dataTable)
    {
        $fields = $dataTable->fields;
        $grid = $dataTable->list_grid;
        $model = $dataTable->config;

        if (gettype($data) == 'object') {
            $data_arr = $data->toArray();
        } else {
            $data_arr = $data;
        }

        $original_data = array_merge($_REQUEST, $data_arr);
        $id = isset($original_data['id']) ? $original_data['id'] : 0;
        $id == 0 && isset($original_data['uid']) && $id = $original_data['uid'];

        foreach ($grid as $name => $g) {
            $val = $db_val = isset($data[$name]) ? $data[$name] : '';
            $field = isset($fields[$name]) ? $fields[$name] : '';
            if (isset($g['href']) && !empty($g['href'])) {
                // 链接支持
                $valArr = [];
                foreach ($g['href'] as $h => $link) {

                    //新的动态标题控制方式
                    if (isset($link['show_set']) && !empty($link['show_set'])) {
                        $check = true;
                        foreach ($link['show_set'] as $set_f => $set_val) {
                            if (empty($set_val)) continue;
                            $set_d = isset($original_data[$set_f]) ? $original_data[$set_f] : '';
                            if (is_array($set_d)) {
                                //计算两个数组的交集
                                $res = array_intersect($set_d, $set_val);
                                if (!$res) {
                                    $check = false;
                                    break;
                                }
                            } else {
                                if (!in_array($set_d, $set_val)) {
                                    $check = false;
                                    break;
                                }
                            }
                        }

                        if (!$check) {
                            unset($g['href'][$h]);
                            continue;
                        }
                    }

                    $href = $link['url'];

                    $show = $link['title'];
                    //旧的动态标题控制方式
//                    if (strpos($show, ':') !== false) {
//                        // 支持标题随状态变化，设置格式：is_show:0|上架,1|下架
//                        list ($show_filed, $show) = explode(':', $show, 2);
//                        $show_val = $original_data[$show_filed];
//                        $showArr = explode(',', $show);
//                        foreach ($showArr as $arr) {
//                            list ($v, $t) = explode('|', $arr);
//                            if ($v == $show_val) {
//                                $show = $t;
//                                break;
//                            }
//                        }
//                    }

                    // 增加跳转方式处理 bctos
                    $target = '_self';
                    if (preg_match('/target=(\w+)/', $href, $matches)) {
                        $target = $matches[1];
                        $href = str_replace('&' . $matches[0], '', $href);
                    }

                    // 替换系统特殊字符串
                    $href = str_replace(array(
                        '[DELETE]',
                        '[EDIT]',
                        '[MODEL]'
                    ), array(
                        'del?id=[id]&model=[MODEL]',
                        'edit?id=[id]&model=[MODEL]',
                        $model['name']
                    ), $href);

                    // 替换数据变量
                    $href = preg_replace_callback('/\[([a-z_]+)\]/', function ($match) use ($original_data) {
                        return isset($original_data[$match[1]]) ? $original_data[$match[1]] : '';
                    }, $href);

                    // 兼容多种写法
                    if (strpos($href, '?') === false && strpos($href, '&') !== false) {
                        $href = preg_replace("/&/i", "?", $href, 1);
                    }

                    if ($show == '删除') {
                        $valArr[] = '<a class="tr-del" data-url="' . urldecode(U($href, $GLOBALS['get_param'])) . '">' . $show . '</a>';
                    } elseif ($show == '复制链接') {
                        $paramArrs = $GLOBALS['get_param'];
                        unset($paramArrs['mdm']);
                        if (!strpos($href, '#')) {
                            $href = U($href);
                        }
                        $valArr[] = '<a class="list_copy_link" id="copyLink_' . $original_data['id'] . '"   data-clipboard-text="' . urldecode($href) . '">' . $show . '</a>';
                    } elseif (!empty($show)) {
                        // 排除GET里的参数影响到已赋值的参数
                        $url_param = array();
                        foreach ($GLOBALS['get_param'] as $key => $gp) {
                            if (strpos($href, $key . '=') === false && $key != 'p') {
                                $url_param[$key] = $gp;
                            }
                        }
                        if (isset($link['class'])) {
                            $valArr[] = '<a  class="' . $link['class'] . '" target="' . $target . '" href="' . urldecode(U($href, $url_param)) . '">' . $show . '</a>';
                        } else {
                            $valArr[] = '<a  target="' . $target . '" href="' . urldecode(U($href, $url_param)) . '">' . $show . '</a>';
                        }
                    }
                }
                $val = implode(' ', $valArr);
            } elseif (!empty($g['function']) && $g['function'] != '') {
                $val = call_user_func($g['function'], $val);
                $data[$name . '_db'] = $db_val;
            } else {
                // get_name_by_status方法不再用，下面按字段类型自动做数据转换，不再需要人工转换
                $valArr = IS_AJAX ? $this->parseDataByFieldByApi($val, $field, $id, $model, $g) : $this->parseDataByField($val, $field, $id, $model, $g);
                $val = $valArr['value'];
                $valArr['has_chang'] && $data[$name . '_db'] = $db_val;
            }

            $data[$name] = $val;
        }
        return $data;
    }

// 获取模型列表数据
    public function getModelList($model = null, $order = 'id desc', $all_field = false)
    {
        if (empty($model)) {
            return $this->error('请先增加数据模型再试');
        }

        $dataTable = D('common/Models')->getFileInfo($model);
        if ($dataTable === false) {
            return $this->error($model['name'] . ' 的模型文件不存在');
        }
        //以文件配置为最高优先级
        if (is_array($model) && is_array($dataTable->config)) {
            $model = array_merge($model, $dataTable->config);
        }

        $this->assign('add_button', $dataTable->config['add_button']);
        $this->assign('del_button', $dataTable->config['del_button']);
        $this->assign('search_button', $dataTable->config['search_button']);
        $this->assign('check_all', $dataTable->config['check_all']);

        // 解析列表规则
        $list_data = $this->listGrid($model, $dataTable);
        $fields = $list_data['fields'];

        // 搜索条件
        $map = $this->searchMap($model, $list_data['db_fields']);
        $row = empty($model['list_row']) ? 20 : $model['list_row'];

        // 读取模型数据列表
        if (empty($order) && isset($_REQUEST['order'])) {
            $order = input('order', 'id') . ' ' . input('by', 'desc');
        }
        if ($model['name'] != 'user') {
            empty($fields) || in_array('id', $fields) || array_push($fields, 'id');
            empty($order) && $order = 'id desc';
        } else {
            empty($fields) || in_array('uid', $fields) || array_push($fields, 'uid');
            empty($order) && $order = 'uid desc';
        }
        // dump ( $order );
        $name = $dataTable->config['name'];
        $db_field = true;
        if (!$all_field && !empty($fields)) {
            $db_field = $fields;
        }

        $wp_where = wp_where($map);
        $data = M($name)->field($db_field)->where($wp_where)->order($order)->paginate($row);

        $list = $this->parsePageData($data, $dataTable, $list_data);
        return $list;
    }

// 解析列表规则
    public function listGrid($model, $obj = false)
    {

        // 过滤重复字段信息
        if ($obj === false) {
            $obj = D('common/Models')->getFileInfo($model);
        }
        if ($obj === false) {
            throw new \think\Exception('数据模型获取失败', 10006);
        }
        //把动态显示需要的判断字段也加入到查询字段中
        foreach ($obj->list_grid as $f => $h) {
            $fields[] = $f;
            if (isset($h['href']) && !empty($h['href'])) {
                foreach ($h['href'] as $url) {
                    if (isset($url['show_set']) && !empty($url['show_set'])) {
                        foreach ($url['show_set'] as $set => $show) {
                            $fields[] = $set;
                        }
                    }
                }
            }
        }
        $model_fields = array_keys($obj->fields);

        in_array('id', $model_fields) || array_push($model_fields, 'id');
        $fields = array_intersect($fields, $model_fields);

        $res['fields'] = array_unique($fields);
        $res['list_grids'] = $obj->list_grid;
        $res['db_fields'] = $model_fields;
        return $res;
    }

    public function searchMap($model, $fields = [])
    {
        $map = [];
        empty($fields) && $fields = [];

        // 插件里的操作自动加上Token限制
        $wpid = get_wpid();
        if (!isset($map['wpid']) && !empty($wpid) && in_array('wpid', $fields) && !(isset($GLOBALS['where_forbit_field']) && $GLOBALS['where_forbit_field'] == 'wpid')) {
            $map['wpid'] = $wpid;
        }

        // 自定义的条件搜索
        if (!empty($GLOBALS['where'])) {
            $map = array_merge($map, $GLOBALS['where']);
        }
        $GLOBALS['where'] = '';
        //兼容旧版本的条件搜索
        $conditon = session('common_condition');
        if (!empty($conditon)) {
            $map = array_merge($map, $conditon);
        }
        session('common_condition', null);

        // 关键字搜索
        $key = $model['search_key'] ? $model['search_key'] : 'title';
        $keyArr = explode(':', $key);
        $key = $keyArr[0];
        $placeholder = isset($keyArr[1]) ? $keyArr[1] : '请输入关键字';
        $this->assign('placeholder', $placeholder);
        $this->assign('search_key', $key);

        // 条件搜索
        $data = input('param.');
        if (!(isset($GLOBALS['where_forbit_field']) && $GLOBALS['where_forbit_field'] == $key) && isset($data[$key]) && !isset($map[$key]) && in_array($key, $fields)) {
            $map[$key] = array(
                'like',
                '%' . htmlspecialchars($data[$key]) . '%'
            );

            // unset($_REQUEST[$key]);
        }

        foreach ($data as $name => $val) {
            if (!(isset($GLOBALS['where_forbit_field']) && $GLOBALS['where_forbit_field'] == $name) && !is_numeric($name) && !isset($map[$name]) && in_array($name, $fields)) {
                $map[$name] = $val;
            }
        }
        unset($data[$key]);

        return $map;
    }


    public function getAddonTemplate($templateFile = '')
    {
        if (file_exists($templateFile)) {
            return $templateFile;
        }

        if (stripos($templateFile, '@') !== false) {
            list($app_name, $templateFile) = explode('@', $templateFile, 2);
        } else {
            $app_name = parse_name(MODULE_NAME);
        }
        if (stripos($templateFile, '/') !== false) {
            list($controller_name, $action_name) = explode('/', $templateFile, 2);
        } else {
            $controller_name = parse_name(CONTROLLER_NAME);
            $action_name = empty($templateFile) ? ACTION_NAME : $templateFile;
        }

        //优先查找自定义主题
        $theme = get_app_info($app_name, 'theme');
        if ($theme) {
            $theme_path = SITE_PATH . '/public/' . $app_name . '/' . $theme . '/' . $controller_name . '/' . $action_name;
            if (file_exists($theme_path . '.php')) {
                return $theme_path . '.php';
            } elseif (file_exists($theme_path . '.html')) {
                return $theme_path . '.html';
            }
        }

        //查找默认主题
        $theme_path = SITE_PATH . '/app/' . $app_name . '/view/' . $controller_name . '/' . $action_name;
        if (file_exists($theme_path . '.php')) {
            return $theme_path . '.php';
        } elseif (file_exists($theme_path . '.html')) {
            return $theme_path . '.html';
        }

        //找不到时查找全站通用主题库
        $theme_path = SITE_PATH . '/app/common/view/base/' . $action_name;
        if (file_exists($theme_path . '.php')) {
            return $theme_path . '.php';
        } elseif (file_exists($theme_path . '.html')) {
            return $theme_path . '.html';
        }

        return '';
    }

// 与post_data函数相比，多了错误判断，省得在业务里重复判断
    public function postData($url, $param, $type = 'json', $return_array = true, $useCert = [])
    {
        $res = post_data($url, $param, $type, $return_array, $useCert);

        // 各种常见错误判断
        if (isset($res['code'])) {
            return $this->error($res['code'] . ': ' . $res['msg']);
        }
        if ($return_array) {
            if (isset($res['errcode']) && $res['errcode'] != 0) {
                return $this->error(error_msg($res));
            } elseif (isset($res['return_code']) && $res['return_code'] == 'FAIL' && isset($res['return_msg'])) {
                return $this->error($res['return_msg']);
            } elseif (isset($res['result_code']) && $res['result_code'] == 'FAIL' && isset($res['err_code']) && isset($res['err_code_des'])) {
                return $this->error($res['err_code'] . ': ' . $res['err_code_des']);
            }
        }
        return $res;
    }

    /**
     * Ajax方式返回数据到客户端，兼容3.0的方法，仅支持JSON
     *
     * @access protected
     * @param mixed $data
     *            要返回的数据
     * @param String $type
     *            AJAX返回数据格式
     * @param int $json_option
     *            传递给json_encode的option参数
     * @return void
     */
    protected function ajaxReturn($data, $type = 'JSON', $json_option = 0)
    {
        header('Content-Type:application/json; charset=utf-8');
        return json($data);
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param mixed $msg 提示信息
     * @param string $url 跳转的URL地址
     * @param mixed $data 返回的数据
     * @param integer $wait 跳转等待时间
     * @param array $header 发送的Header信息
     * @return void
     */
    protected function success($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        //dump(gettype($url));
        if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
            $url = $_SERVER["HTTP_REFERER"];
        } elseif ('' !== $url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : U($url);
        }
        //dump($url);exit;
        $result = [
            'code' => 0,
            'msg' => $msg,
            'data' => $data,
            'url' => $url,
            'wait' => $wait,
        ];

        $type = $this->getResponseType();

        // 把跳转模板的渲染下沉，这样在 response_send 行为里通过getData()获得的数据是一致性的格式
        if ('html' == strtolower($type)) {
            $type = 'jump';
        }

        return $this->showMsg($result, $type);
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param mixed $msg 提示信息
     * @param string $url 跳转的URL地址
     * @param mixed $data 返回的数据
     * @param integer $wait 跳转等待时间
     * @param array $header 发送的Header信息
     * @return void
     */
    protected function error($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        $type = $this->getResponseType();
        if (is_null($url)) {
            $url = IS_AJAX ? '' : 'javascript:history.back(-1);';
        } elseif ('' !== $url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : U($url);
        }

        $result = [
            'code' => 1,
            'msg' => $msg,
            'data' => $data,
            'url' => $url,
            'wait' => $wait,
        ];

        if ('html' == strtolower($type)) {
            $type = 'jump';
        }

        return $this->showMsg($result, $type, 'app.dispatch_error_tmpl');
    }

    function showMsg($result, $type, $temp = 'app.dispatch_success_tmpl')
    {
        if ($type == 'json') {
            return $this->ajaxReturn($result);
        } else {
            $result['type'] = $type;
            $result['template'] = config($temp);

            abort(200, json_encode($result));
        }
    }

    /**
     * URL重定向
     * @access protected
     * @param string $url 跳转的URL表达式
     * @param array|integer $params 其它URL参数
     * @param integer $code http code
     * @param array $with 隐式传参
     * @return void
     */
    protected function redirect($url, $params = [], $code = 302)
    {
        $url = U($url, $params);
        $res = redirect($url, $code);
        return $res;
    }

    /**
     * 获取当前的response 输出类型
     * @access protected
     * @return string
     */
    protected function getResponseType()
    {
        return IS_AJAX
            ? config('app.default_ajax_return')
            : config('app.default_return_type');
    }

    /**
     * 重写模板显示 调用内置的模板引擎显示方法，
     *
     * @access protected
     * @param string $template
     *            模板文件名
     * @param array $vars
     *            模板输出变量
     * @param array $replace
     *            模板替换
     * @param array $config
     *            模板参数
     * @return mixed
     */
//    protected function fetch($template = '', $vars = [], $config = [])
//    {
//        $template = $this->getAddonTemplate($template);
//
//        $config['tpl_replace_string'] = config('template.tpl_replace_string');
//        return parent::fetch($template, $vars, $config);
//    }
    function fetch($template = '')
    {
        $template = $this->getAddonTemplate($template);

        return View::fetch($template);
    }

    function assign($name, $value = '')
    {
        return View::assign($name, $value);
    }

    function setStatus()
    {
        $table = input('table');
        $id = id();
        $field = input('field', 'status');

        $info = M($table)->where('id', $id)->find();
        if (isset($info[$field])) {
            $val = 1 - $info[$field];
            M($table)->where('id', $id)->update([$field => $val]);

            return $this->success('设置成功');
        } else {
            return $this->error('数据不存在');
        }
    }

    //根据结果res判断返回success还是error
    function msg($res, $msg = '操作', $url = null)
    {
        if ($res !== false) {
            return $this->success($msg . '成功', $url);
        } else {
            return $this->error($msg . '失败', $url);
        }
    }

    //实现列表中开关按钮更新操作
    function switchAjaxUpdate()
    {
        //TODO 权限判断

        $table = input('table');
        $field = input('field');
        $value = input('value');

        if ($table == 'attribute') {
            $name = input('name');
            $model_id = input('model_id');

            $dao = D('common/Models');

            $obj = $dao->getFileInfo($model_id);

            $list = $obj->fields;

            $data = $list[$name];
            $data['name'] = $name;
            $data['model_id'] = $model_id;
            $old = $data;
            $data[$field] = $value;

            if ($value == 1 && strpos($data['field'], 'NOT NULL') === false) {
                $data['field'] = str_replace('NULL', 'NOT NULL', $data['field']);
            } elseif ($value == 0 && strpos($data['field'], 'NOT NULL') !== false) {
                $data['field'] = str_replace('NOT NULL', 'NULL', $data['field']);
            }

            // dump ( $listArr );
            if (!isset($obj->config['inherit']) || $obj->config['inherit'] != 'common_category') { //继承分类基类的表不需要创建数据表
                $res = $dao->updateField($data, $old);
                if (!$res) {
                    return $this->error('140068:更新字段出错！');
                }
            }

            // 删除字段缓存文件
            // dump ( $data );
            $model = $dao->field(true)->find($model_id);

            $list[$name] = $data;
            // dump ( $newList );
            // exit ();
            $dao->buildFile($model, $list);

            // 记录行为
            action_log('update_attribute', 'attribute', '', get_mid());
        } else {
            $id = input('id');
            $where_field = $table == 'user' ? 'uid' : 'id';
            M($table)->where($where_field, $id)->update([$field => $value]);
        }
        return $this->success('保存成功');
    }
}
