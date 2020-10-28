<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星 <xw@bctos.cn> <QQ:203163051>
// +----------------------------------------------------------------------
namespace app\admin\controller;

/**
 * 扩展后台管理页面
 *
 * @author yangweijie <yangweijiester@gmail.com>
 */
class Apps extends Admin
{

    public function initialize()
    {
        $this->assign('_extra_menu', array(
            '已装应用后台' => D('Apps')->getAdminList()
        ));
        parent::initialize();
    }

    function cleanModel()
    {
        $list = M('model')->select();
        $ids = [];
        foreach ($list as $vo) {

            if (empty($vo['addon']) || ($vo['addon'] != 'core' && $vo['addon'] != 'admin' && !is_install($vo['addon']))) {
                $ids[] = $vo['id'];
            }
        }
        if (!empty($ids)) {
            M('model')->whereIn('id', $ids)->delete();
        }
        dump($ids);
    }

    // 创建向导首页
    public function create()
    {
        if (!is_writable(SITE_PATH . '/app/')) {
            return $this->error('您没有创建目录写入权限，无法使用此功能');
        }

        $this->meta_title = '创建向导';

        return $this->fetch('create');
    }

    public function checkForm()
    {
        $data = input('post.');

        $app_name = parse_name($data['info']['name']);
        $data['info']['name'] = trim($data['info']['name']);
        if (!$data['info']['name']) {
            return $this->error('应用标识必须');
        }

        // 检测应用名是否合法
        $addons_dir = SITE_PATH . '/app/';
        if (file_exists("{$addons_dir}{$app_name}")) {
            return $this->error('应用已经存在了');
        }
        return $this->success('可以创建');
    }

    public function build()
    {
        $data = input('post.');
        $data['info']['name'] = trim($data['info']['name']);
        $addons_dir = SITE_PATH . '/app/';
        $app_name = parse_name($data['info']['name']);
        $appName = parse_name($data['info']['name'], 1);

        // 创建目录结构
        $files = [];
        $addon_dir = "$addons_dir{$app_name}/";
        $files[] = $addon_dir;
        if (isset($data['has_config']) && $data['has_config'] == 1) {
            $files[] = $addon_dir . 'config.php';
        }

        if ($data['has_outurl']) {
            $files[] = "{$addon_dir}controller/";
            $files[] = "{$addon_dir}controller/{$appName}.php";
            $files[] = "{$addon_dir}controller/Wap.php";
            $files[] = "{$addon_dir}model/";
            $files[] = "{$addon_dir}model/{$appName}.php";
            $files[] = "{$addon_dir}model/Service.php";
            $files[] = "{$addon_dir}view/";
            $files[] = "{$addon_dir}view/{$app_name}/";
            $files[] = "{$addon_dir}view/wap/";
            $files[] = "{$addon_dir}data_table/";
        }

        create_dir_or_files($files);

        // 写文件
        if ($data['has_outurl']) {
            $addonController = <<<str
<?php

namespace app\\{$app_name}\controller;
use app\common\controller\WebBase;

//PC运营管理端的控制器
class {$appName} extends WebBase{

}

str;
            file_put_contents("{$addon_dir}controller/{$appName}.php", $addonController);
            $addonController = <<<str
<?php

namespace app\\{$app_name}\controller;
use app\common\controller\WapBase;

//手机H5版的控制器
class Wap extends WapBase{

}

str;
            file_put_contents("{$addon_dir}controller/Wap.php", $addonController);
            $addonModel = <<<str
<?php

namespace app\\{$app_name}\model;
use app\common\model\Base;


 //{$data['info']['name']}模型
class {$appName} extends Base{

}

str;
            file_put_contents("{$addon_dir}model/{$appName}.php", $addonModel);

            $serviceModel = <<<str
<?php

namespace app\\{$app_name}\model;
use app\common\model\ServiceBase;

//应用对外提供服务的接口
class Service extends ServiceBase{

}

str;
            file_put_contents("{$addon_dir}model/Service.php", $serviceModel);
        }
        $data['has_adminlist'] = isset($data['has_adminlist']) ? $data['has_adminlist'] : '""';
        $addonModel = <<<str
<?php

namespace app\\{$app_name};
use app\common\controller\InfoBase;

/**
 * {$appName}应用
 */
class Info extends InfoBase{
    public \$info = array(
            'name'=>'{$app_name}',
            'title'=>'{$data['info']['title']}',
            'description'=>'{$data['info']['description']}',
            'author'=>'{$data['info']['author']}',
            'version'=>'{$data['info']['version']}',
            'has_adminlist'=>{$data['has_adminlist']}
        );

    //自定义权限规则
    public \$auth_rule = [];

    //自定义积分规则
    public \$credit_config = [];

    //自定义入口地址,默认是lists或者config
    public \$init_url = [];

    function reply(\$dataArr, \$keywordArr = []) {
        \$config = getAppConfig ( '{$data['info']['name']}' ); // 获取后台应用的配置参数
        //dump(\$config);
    }

    public function install() {
        \$install_sql = SITE_PATH.'/app/{$app_name}/install.sql';
        if (file_exists ( \$install_sql )) {
            execute_sql_file ( \$install_sql );
        }
        return true;
    }
    public function uninstall() {
        \$uninstall_sql = SITE_PATH.'/app/{$app_name}/uninstall.sql';
        if (file_exists ( \$uninstall_sql )) {
            execute_sql_file ( \$uninstall_sql );
        }
        return true;
    }
}

str;
        file_put_contents("{$addon_dir}/Info.php", $addonModel);
        if ($data['has_adminlist'] == '1') {
            //创建默认的数据模型
            $model['name'] = $app_name;
            $model['title'] = $data['info']['title'];
            $model['engine_type'] = 'InnoDB';
            $model['need_pk'] = '1';
            $model['addon'] = $appName;
            D('common/Models')->buildFileByData($model);

            //创建默认数据表
            $table_name = DB_PREFIX . $app_name;
            $sql = <<<sql
                CREATE TABLE IF NOT EXISTS `{$table_name}` (
                `id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键' ,
                PRIMARY KEY (`id`)
                )ENGINE={$model['engine_type']} DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci CHECKSUM=0 ROW_FORMAT=DYNAMIC DELAY_KEY_WRITE=0;
sql;
            M()::execute($sql);
        }

        return $this->install($appName);
    }

    /**
     * 应用列表
     */
    public function index()
    {
        $this->meta_title = '应用应用列表';

        $cate_id = I('cate_id/d', 0);
        $this->assign('cate_id', $cate_id);

        $list = D('Apps')->getList();
        if ($cate_id != 0) {
            $cid = $cate_id == -1 ? 0 : $cate_id;
            foreach ($list as $k => $v) {
                $id = intval($v['cate_id']);
                if ($id != $cid) {
                    unset($list[$k]);
                }
            }
        }
        $title = I('title');
        if (!empty($title)) {
            foreach ($list as $k => $v) {
                if (stripos($v['name'], $title) === false && stripos($v['title'], $title) === false) {
                    unset($list[$k]);
                }
            }
        }

        // 分类
        foreach ($list as &$v) {
            if (!isset($v['is_show_text'])) {
                $v['is_show_text'] = '否';
            }
        }
        // dump($list);
        $this->assign('_list', $list);
        $this->assign('_page', '');
        // 记录当前列表页的cookie
        $forward = cookie('__forward__');
        empty($forward) && cookie('__forward__', $_SERVER['REQUEST_URI']);
        return $this->fetch();
    }

    public function set_show($id, $val)
    {
        D('Apps')->set_show($id, $val);
        return $this->success('设置成功');
    }

    /**
     * 应用后台显示页面
     *
     * @param string $name
     *            应用名
     */
    public function adminList($name)
    {
        // 记录当前列表页的cookie
        $forward = cookie('__forward__');
        empty($forward) && cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->assign('name', $name);
        $class = get_addon_class($name);
        if (!class_exists($class)) {
            return $this->error('应用不存在');
        }

        $addon = new $class();
        $this->assign('addon', $addon);
        $param = $addon->admin_list;
        if (!$param) {
            return $this->error('应用列表信息不正确');
        }

        $this->meta_title = $addon->info['title'];

        $this->assign('title', $addon->info['title']);
        $this->assign($param);
        if (!isset($fields)) {
            $fields = '*';
        }

        if (!isset($param['search_key'])) {
            $key = 'title';
        } else {
            $key = $param['search_key'];
        }

        $_REQUEST = input('param.');
        if (isset($_REQUEST[$key])) {
            $map[$key] = array(
                'like',
                '%' . $_GET[$key] . '%'
            );
            unset($_REQUEST[$key]);
        }

        if (isset($model)) {
            $model = D("{$name}/{$model}");
            // 条件搜索
            $map = [];
            foreach ($_REQUEST as $name => $val) {
                if ($fields == '*') {
                    $fields = $model->getDbFields();
                }
                if (in_array($name, $fields)) {
                    $map[$name] = $val;
                }
            }
            if (!isset($order)) {
                $order = '';
            }

            $list = $this->lists_data($model->field($fields), $map, $order);
            $fields = [];
            foreach ($param['list_grid'] as &$value) {
                // 字段:标题:链接
                $val = explode(':', $value);
                // 支持多个字段显示
                $field = explode(',', $val[0]);
                $value = array(
                    'field' => $field,
                    'title' => $val[1]
                );
                if (isset($val[2])) {
                    // 链接信息
                    $value['href'] = $val[2];
                    // 搜索链接信息中的字段信息
                    preg_replace_callback('/\[([a-z_]+)\]/', function ($match) use (&$fields) {
                        $fields[] = $match[1];
                    }, $value['href']);
                }
                if (strpos($val[1], '|')) {
                    // 显示格式定义
                    list ($value['title'], $value['format']) = explode('|', $val[1]);
                }
                foreach ($field as $val) {
                    $array = explode('|', $val);
                    $fields[] = $array[0];
                }
            }
            $this->assign('model', $model->model);
            $this->assign('list_grid', $param['list_grid']);
        }
        $this->assign('_list', $list);
        if ($addon->custom_adminlist) {
            $this->assign('custom_adminlist', $this->fetch($addon->addon_path . $addon->custom_adminlist));
        }

        return $this->fetch('adminlist');
    }

    /**
     * 启用应用
     */
    public function enable()
    {
        $id = I('id');
        $msg = array(
            'success' => '启用成功',
            'error' => '启用失败'
        );
        D('common/Menu')->appStatusChang($id, 'enable');
        return $this->resume('Apps', "id={$id}", $msg);
    }

    /**
     * 禁用应用
     */
    public function disable()
    {
        $id = I('id');
        $msg = array(
            'success' => '禁用成功',
            'error' => '禁用失败'
        );
        D('common/Menu')->appStatusChang($id, 'disable');
        return $this->forbid('Apps', "id={$id}", $msg);
    }

    /**
     * 解析数据库语句函数
     *
     * @param string $sql
     *            sql语句 带默认前缀的
     * @param string $tablepre
     *            自己的前缀
     * @return multitype:string 返回最终需要的sql语句
     */
    public function sql_split($sql, $tablepre)
    {
        if ($tablepre != "onethink_") {
            $sql = str_replace("onethink_", $tablepre, $sql);
        }

        $sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8", $sql);

        $sql = str_replace("\r", "\n", $sql);
        $ret = [];
        $num = 0;
        $queriesarray = explode(";\n", trim($sql));
        unset($sql);
        foreach ($queriesarray as $query) {
            $ret[$num] = '';
            $queries = explode("\n", trim($query));
            $queries = array_filter($queries);
            foreach ($queries as $query) {
                $str1 = substr($query, 0, 1);
                if ($str1 != '#' && $str1 != '-') {
                    $ret[$num] .= $query;
                }
            }
            $num++;
        }
        return $ret;
    }

    public function menu()
    {
        $id = I('id');
        $app_info = D('Apps')->where('id', $id)->find();

        $map['url_type'] = $data['url_type'] = 0;
        $map['addon_name'] = $data['addon_name'] = $app_info['name'];

        if (IS_POST) {
            $type = I('type');
            $pid = I('pid');

            $data['title'] = $app_info['title'];
            if ($type == 0) {
                // 隐藏
                $data['pid'] = 0;
                $data['is_hide'] = 1;
            } elseif ($type == 1) {
                // 顶部一级菜单
                $data['pid'] = 0;
                $data['is_hide'] = 0;
            } else {
                // 侧栏二级菜单
                $data['pid'] = $pid;
                $data['is_hide'] = 0;
            }
            $data['rule'] = implode(',', input('rule'));
            $info = D('common/Menu')->where(wp_where($map))->find();
            if ($info) {
                D('common/Menu')->updateMenuData($data, $map);
            } else {
                $data['target'] = '_self';
                D('common/Menu')->addData($data);
            }
            D('common/Menu')->clearCache(0);

            return $this->success('保存成功', U('index'));
        } else {
            $menu_id = D('common/Menu')->where(wp_where($map))->value('id');
            if ($menu_id > 0) {
                $rule = D('AuthGroup')->getRuleByMenuId($menu_id);
            } else {
                $rule = '';
            }
            $this->assign('rule', $rule);

            // 获取一级菜单列表
            unset($map);
            $map['pid'] = 0;
            $map['place'] = 0;
            $map['is_hide'] = 0;
            $menu_list = find_data(D('common/Menu')->where(wp_where($map))->select());
            $this->assign('menu_list', $menu_list);
            $this->assign('id', $id);
            $title = urldecode(input('title'));
            if (empty($title)) {
                $app_info = D('Apps')->where('id', $id)->find();
                $title = $app_info['title'];
            }
            $this->assign('title', $title);


        }

        return $this->fetch();
    }

    /**
     * 安装应用
     */
    public function install($addon_name = '')
    {
        if (empty($addon_name)) {
            $addon_name = trim(I('addon_name'));
        }
        $addon_name = parse_name($addon_name, 0);

        $class = get_addon_class($addon_name);
        if (!class_exists($class)) {
            return $this->error('应用不存在');
        }

        $addons = new $class();
        $info = $addons->info;
        if (!$info) // 检测信息的正确性
        {
            return $this->error('应用信息缺失');
        }

        $info['type'] = 0;
        $info['name'] = parse_name($info['name'], 0);
        session('addons_install_error', null);
        $install_flag = $addons->install();
        if (!$install_flag) {
            return $this->error('执行应用预安装操作失败' . session('addons_install_error'));
        }

        // 自定义权限规则安装
        if (!empty($addons->auth_rule)) {
            $auth_dao = D('common/AuthRule');
            $auth_data['mod'] = $addon_name;
            $auth_data['type'] = 'custom_app';
            $auth_dao->delData($auth_data);
            foreach ($addons->auth_rule as $name => $title) {
                $auth_data['title'] = $title;
                $auth_data['name'] = $name;
                $insert_all[] = $auth_data;
            }

            $auth_dao->saveAll($insert_all);
        }

        // 自定义积分规则安装
        if (!empty($addons->credit_config)) {
            $credit_dao = D('common/CreditConfig');

            $credit_data['mod'] = $addon_name;
            $credit_dao->delData($credit_data);

            $credit_data['mTime'] = NOW_TIME;
            foreach ($addons->credit_config as $name => $vo) {
                $credit_data['name'] = $name;

                $credit_data['title'] = $vo[0];
                isset($vo[1]) && $credit_data['score'] = $vo[1];
                isset($vo[2]) && $credit_data['type'] = $vo[2];

                $credit_dao->addData($credit_data);
            }
        }

        $info['config'] = json_encode(getAppConfig($addon_name));
        $addonsModel = D('Apps');

        $res = $addonsModel->insertGetId($info);
        if ($res) {
            $url = U('menu', [
                'id' => $res,
                'title' => $addons->info['title']
            ]);
            //判断是否已经存在菜单
            $hasMenu = M('menu')->where('url_type', 0)->where('addon_name', $addon_name)->value('id');
            if ($hasMenu > 0) {
                $url = U('index');
            }

            // 初始化菜单
            return $this->success('安装成功', $url);
        } else {
            return $this->error('写入应用数据失败');
        }
    }

    /**
     * 卸载应用
     */
    public function uninstall()
    {
        $id = trim(I('id'));
        $db_addons = M('apps')->where('id', $id)->find();
        $class = get_addon_class($db_addons['name']);
        $this->assign('jumpUrl', U('index'));
        if (!$db_addons || !class_exists($class)) {
            return $this->error('应用不存在');
        }

        session('addons_uninstall_error', null);
        $addons = new $class();
        $uninstall_flag = $addons->uninstall();
        if (!$uninstall_flag) {
            return $this->error('执行应用预卸载操作失败' . session('addons_uninstall_error'));
        }
        // 删除菜单
        D('common/Menu')->appStatusChang($id, 'del');

        $delete = M('apps')->where("name='{$db_addons['name']}'")->delete();
        if ($delete === false) {
            return $this->error('卸载应用失败');
        } else {
            // 自定义权限规则卸载
            if (!empty($addons->auth_rule)) {
                $auth_dao = D('common/AuthRule');
                $auth_data['mod'] = $db_addons['name'];
                $auth_data['type'] = 'custom_app';
                $auth_dao->delData($auth_data);
            }

            // 自定义积分规则卸载
            if (!empty($addons->credit_config)) {
                $credit_dao = D('common/CreditConfig');

                $credit_data['mod'] = $db_addons['name'];
                $credit_dao->delData($credit_data);
            }

            return $this->success('卸载成功');
        }
    }

    public function edit()
    {
        $name = I('name');
        $id = I('id');
        $this->assign('name', $name);
        $class = get_addon_class($name);
        if (!class_exists($class)) {
            return $this->error('应用不存在');
        }

        $addon = new $class();
        $this->assign('addon', $addon);
        $param = $addon->admin_list;
        if (!$param) {
            return $this->error('应用列表信息不正确');
        }

        $this->assign('title', $addon->info['title']);
        if (isset($param['model'])) {
            $addonModel = D("{$name}/{$param['model']}");
            if (!$addonModel) {
                return $this->error('模型无法实列化');
            }

            $model = $addonModel->model;
            $this->assign('model', $model);
        }
        if ($id) {
            $data = $addonModel->where('id', $id)->find();
            if (empty($data))
                return $this->error('数据不存在！');
            $this->assign('data', $data);
        }

        if (request()->isPost()) {
            $data = input('post.');

            if ($id) {
                $flag = $addonModel->where('id', $id)->save($data);
                if ($flag !== false) {
                    return $this->success("编辑{$model['title']}成功！", cookie('__forward__'));
                } else {
                    return $this->error($addonModel->getError());
                }
            } else {
                $flag = $addonModel->save($data);
                if ($flag) {
                    return $this->success("添加{$model['title']}成功！", cookie('__forward__'));
                }
            }
            return $this->error($addonModel->getError());
        } else {
            $fields = $addonModel->_fields;
            $this->assign('fields', $fields);
            $this->meta_title = $id ? '编辑' . $model['title'] : '新增' . $model['title'];
            if ($id) {
                $template = $model['template_edit'] ? $model['template_edit'] : '';
            } else {
                $template = $model['template_add'] ? $model['template_add'] : '';
            }

            if ($template) {
                return $this->fetch($addon->addon_path . $template);
            } else {
                return $this->fetch();
            }
        }
    }

    public function del()
    {
        $name = I('name');
        $id = I('id');
        $ids = array_unique((array)I('ids', 0));

        if (empty($ids)) {
            return $this->error('请选择要操作的数据!');
        }

        $class = get_addon_class($name);
        if (!class_exists($class)) {
            return $this->error('应用不存在');
        }

        $addon = new $class();
        $param = $addon->admin_list;
        if (!$param) {
            return $this->error('应用列表信息不正确');
        }

        if (isset($param['model'])) {
            $addonModel = D("{$name}/{$param['model']}");
            if (!$addonModel) {
                return $this->error('模型无法实列化');
            }
        }

        if ($addonModel->whereIn('id', $ids)->delete()) {
            D('common/Menu')->appStatusChang($id, 'del');
            return $this->success('删除成功');
        } else {
            return $this->error('删除失败！');
        }
    }
}
