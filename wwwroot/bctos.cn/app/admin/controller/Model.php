<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

/**
 * 模型管理控制器
 *
 * @author huajie <banhuajie@163.com>
 */
class Model extends Admin
{
    /**
     * 模型管理首页
     *
     * @author huajie <banhuajie@163.com>
     */
    public function index()
    {
        $dao = D('common/Models');
        // 根据文件查找还没安装的模型
        $files = $this->file_scan();
        session('file_scan', $files);

        $modelArr = $dao->column('id', 'name');
        $objArr = $needImportModel = [];
        foreach ($files as $index => $file) {
            $info = pathinfo($file);
            $class = str_replace('.class', '', $info['filename']);
            if (isset($objArr[$class])) {
                continue;
            }

            require_once $file;
            $objArr[$class] = new $class();
            $config = $objArr[$class]->config;
            if (!isset($modelArr[$config['name']])) {
                $config['id'] = 0 - $index;
                $needImportModel[$config['name']] = $config;
            }
        }

        // 自动加载数据模型文件
        $map = [];
        if (isset($_GET['title'])) {
            $title = I('title');
            $map['name'] = array(
                'like',
                "%$title%"
            );
        }
        $app = input('app');
        if (!empty($app)) {
            $map['addon'] = $app;
        }
        $this->assign('app', $app);

        $list = $this->lists_data('model', $map);
        if (empty($map) && !empty($needImportModel)) {
            $list = array_merge($needImportModel, $list);
        }

        $addonArr = $this->_get_all_addon();

        foreach ($list as $k => &$vo) {
            $file = $dao->requireFile($vo);
            if (!$file) {
                continue;
            }

            $file_md5 = md5_file($file);
            $vo['update_db'] = !empty($vo['file_md5']) && ($vo['file_md5'] != $file_md5) ? 1 : 0;

            $name = parse_name($vo['name'], 1);
            $class = $name . 'Table';
            $obj = $objArr[$class];

            $data = $this->getDBInfo($vo['name'], $obj->fields, '');
            foreach ($data as $d => $f) {
                foreach ($f as $k => $i) {
                    if ($i == '' || $i == '请输入内容') {
                        unset($data[$d][$k]);
                    }
                }
            }

            if (isset($needImportModel[$vo['name']])) {
                // 数据表不存在
                $vo['table_exists'] = 0;
            } else {
                $vo['table_exists'] = 1;
            }


            if ($res = $this->diff_data($obj->fields, $data)) {
//                dump($vo['name']);
//                dump($res);
                $vo['update_file'] = 1;
            } else {
                $vo['update_file'] = 0;
            }

            $addon = parse_name($vo['addon']);
            if (!empty($addon) && isset($addonArr[$addon])) {
                $vo['addon_title'] = $addonArr[$addon];
            } else {
                $vo['addon_title'] = '';
            }
        }

        int_to_string($list);
        // 记录当前列表页的cookie
        $forward = cookie('__forward__');
        empty($forward) && cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('_list', $list);
        $this->meta_title = '模型管理';
        return $this->fetch();
    }

    function diff_data($old, $new)
    {
        //dump($old);dump($new);exit;
        if (count($old) != count($new)) return 1;

        foreach ($old as $f => $v) {
            if (!isset($new[$f])) return 2;

            //比较键数
            if (count($v) != count($new[$f])) {
//                dump($v);
//                dump($new[$f]);
                return 3;
            }

            //比较值
            foreach ($v as $ff => $vv) {
                if (!isset($new[$f][$ff])) return 4;

                //比较值
                if ($this->lowerEmpty($vv) != $this->lowerEmpty($new[$f][$ff])) {
                    return 5;
                }
            }
        }

        return 0;
    }

    function lowerEmpty($val)
    {
        if (!is_string($val) && !is_numeric($val)) {
            $val = json_encode($val);
        }

        return strtolower(str_replace(' ', '', $val));
    }

    function inportCms()
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(0);
        }
        $dao = D('common/Models');

        $map = [];
        if (isset($_GET['model_id'])) {
            $map['id'] = I('model_id');
        }
        $map['name'] = ['like', 'cms_%'];
        $list = find_data($dao->where(wp_where($map))->select());
        foreach ($list as $vo) {
            // dump ( $vo );
            $obj = $dao->getFileInfo($vo);

            $config = [
                'name' => $vo['name'],
                'title' => $vo['title'],
                'search_key' => $vo['search_key']
            ];

            if ($obj == false) {
                $list_grid_arr = $fields = [];
                $config['add_button'] = $config['del_button'] = $config['search_button'] = $config['check_all'] = 1;
            } else {
                $list_grid_arr = $obj->list_grid;
                $fields = $obj->fields;
                $config['add_button'] = $obj->config['add_button'];
                $config['del_button'] = $obj->config['del_button'];
                $config['search_button'] = $obj->config['search_button'];
                $config['check_all'] = $obj->config['check_all'];
            }

            $config['list_row'] = $vo['list_row'];
            $config['addon'] = $vo['addon'];

            $data = $this->getDBInfo($vo['name'], $fields, 'dbtofile');

            // 保持字段排序
            $newList = [];
            foreach ($fields as $name => $f) {
                if (isset($data[$name])) {
                    $newList[$name] = $data[$name];
                    unset($data[$name]);
                }
            }

            // 新增加的字段放最后
            foreach ($data as $name => $f) {
                $newList[$name] = $f;
            }

            $dao->buildFile($vo, $newList, $list_grid_arr, $config);
            unset($vo);
        }
        // exit ();
        return $this->success('更新完成', U('index'));
    }

    public function freshDBtoFile()
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(0);
        }
        $dao = D('common/Models');
        $map = [];
        if (isset($_GET['model_id'])) {
            $map['id'] = I('model_id');
        }
        $list = find_data($dao->where($map)->select());
        foreach ($list as $vo) {
            // dump ( $vo );
            $obj = $dao->getFileInfo($vo);

            $config = [
                'name' => $vo['name'],
                'title' => $vo['title'],
                'search_key' => $vo['search_key']
            ];

            if ($obj == false) {
                $list_grid_arr = $fields = [];
                $config['add_button'] = $config['del_button'] = $config['search_button'] = $config['check_all'] = 1;
            } else {
                $list_grid_arr = $obj->list_grid;
                $fields = $obj->fields;
                $config['add_button'] = $obj->config['add_button'];
                $config['del_button'] = $obj->config['del_button'];
                $config['search_button'] = $obj->config['search_button'];
                $config['check_all'] = $obj->config['check_all'];
            }

            $config['list_row'] = $vo['list_row'];
            $config['addon'] = $vo['addon'];

            $data = $this->getDBInfo($vo['name'], $fields, 'dbtofile');

            // 保持字段排序
            $newList = [];
            foreach ($fields as $name => $f) {
                if (isset($data[$name])) {
                    $newList[$name] = $data[$name];
                    unset($data[$name]);
                }
            }

            // 新增加的字段放最后
            foreach ($data as $name => $f) {
                $newList[$name] = $f;
            }

            $dao->buildFile($vo, $newList, $list_grid_arr, $config);
            unset($vo);
        }
        // exit ();
        return $this->success('更新完成', U('index'));
    }

    public function all()
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(0);
        }
        $list = find_data(M('model_copy')->select());
        foreach ($list as $vo) {
            $this->buildFile($vo);
        }
    }

    public function buildFile($vo = [])
    {
        $dao = D('common/Models');
        if (empty($vo)) {
            if (function_exists('set_time_limit')) {
                set_time_limit(0);
            }
            $id_map[] = I('id/d', 0);
            $vo = M('model_copy')->where($id_map)->find();
        }

        $model_map['name'] = $vo['name'];
        if (!$dao->where($model_map)->find()) {
            $add = $vo;
            unset($add['id']);
            $dao->removeOption('data')->insert($add);
        }
        // dump ( $vo );
        $map['model_id'] = $vo['id'];
        $fields = find_data(M('attribute_copy')->where($map)->select());
        // dump ( $fields );exit;
        // =================================================//
        $list_grid = wp_explode($vo['list_grid'], "\r\n");

        $list_grid_arr = [];
        foreach ($list_grid as $v) {
            $func = $href = $title = '';
            $hrefArr = [];
            list ($field, $title) = explode(':', $v, 2);
            if (strpos($field, '|') !== false) {
                list ($field, $func) = explode('|', $field, 2);
            }
            if (strpos($title, '操作') !== false) {
                $field = 'urls';
                $href = str_replace('操作:', '', $title);
                $title = '操作';
                $arr1 = explode(',', $href);
                foreach ($arr1 as $k => $h) {
                    $arr2 = explode('|', $h);
                    $hrefArr[$k]['title'] = $arr2[1];
                    $hrefArr[$k]['url'] = $arr2[0];
                }
            }

            if (empty($hrefArr)) {
                $list_grid_arr[$field] = [
                    'title' => $title,
                    'function' => $func
                ];
            } else {
                $list_grid_arr[$field] = [
                    'title' => $title,
                    'function' => $func,
                    'come_from' => 1,
                    'href' => $hrefArr
                ];
            }
        }

        $config = [
            'name' => $vo['name'],
            'title' => $vo['title'],
            'search_key' => $vo['search_key'],
            'add_button' => 1,
            'del_button' => 1,
            'search_button' => 1,
            'check_all' => 1,
            'list_row' => $vo['list_row'],
            'addon' => $vo['addon']
        ];

        // =================================================//
        // 组装数据
        $newList = $newList2 = [];
        foreach ($fields as $v) {
            $name = $v['name'];

            $data = [];

            $data['title'] = $v['title'];
            $data['field'] = $v['field'];
            $data['type'] = $v['type'];
            empty($v['placeholder']) || $data['placeholder'] = $v['placeholder'];
            empty($v['remark']) || $data['remark'] = $v['remark'];
            empty($v['is_show']) || $data['is_show'] = $v['is_show'];
            empty($v['extra']) || $data['extra'] = $v['extra'];

            if (!empty($v['validate_rule'])) {
                $data['validate_type'] = $v['validate_type'];
                $data['validate_rule'] = $v['validate_rule'];
                $data['validate_time'] = $v['validate_time'];
                $data['error_info'] = $v['error_info'];
            }
            if (!empty($v['auto_rule'])) {
                $data['auto_rule'] = $v['auto_rule'];
                $data['auto_time'] = $v['auto_time'];
                $data['auto_type'] = $v['auto_type'];
            }

            $newList[$name] = $data;
        }
        // 保持字段排序
        if (!empty($vo['field_sort'])) {
            $data = json_decode($vo['field_sort']);

            foreach ($data as $name) {
                if (isset($newList[$name])) {
                    $newList2[$name] = $newList[$name];
                    unset($newList[$name]);
                }
            }

            // 新增加的字段放最后
            foreach ($newList as $name => $f) {
                $newList2[$name] = $f;
            }
        } else {
            $newList2 = $newList;
        }
        // dump ( $vo );
        // dump ( $newList2 );
        // dump ( $list_grid_arr );
        // dump ( $config );
        $dao->buildFile($vo, $newList2, $list_grid_arr, $config);
        unset($vo);
    }

    public function freshAttrtoFile()
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(0);
        }
        $dao = D('common/Models');
        $list = find_data($dao->select()); // ->where ( 'id=123' )
        foreach ($list as $vo) {
            // dump ( $vo );
            $map['model_id'] = $vo['id'];
            $fields = find_data(M('attribute_copy')->where($map)->select());

            $obj = $dao->getFileInfo($vo);
            $oldFields = $obj->fields;
            // 组装数据
            $newList = [];
            foreach ($fields as $v) {
                $name = $v['name'];
                if (!isset($oldFields[$name])) {
                    continue;
                }

                $data = [];
                $info = $oldFields[$name];
                // 'remark' => '', // 字段备注(用于表单中的提示]
                // 'is_show' => '1', // 是否需要在表单里 1:始终显示 2:新增显示 3:编辑显示 5:条件控件 4:隐藏值 0:不显示

                // // 以下高级选项不用时可以去掉
                // 'validate_type' => 'regex', // 验证方式 regex:正则验证 function:函数验证 unique:唯一验证 :length:长度验证 in:验证在范围内 notin:验证不在范围内 between:区间验证 notbetween：不在区间验证
                // 'validate_rule' => '', // 验证规则（根据验证方式定义相关验证规则）,为空表示不验证
                // 'validate_time' => '3', // 验证时间 0:无 3:始 终 1:新 增 2:编 辑
                // 'error_info' => '', // 验证失败出错提示

                // 'auto_rule' => '', // 自动完成规则（根据完成方式订阅相关规则）
                // 'auto_time' => '3', // 自动完成时间 0:无 3:始 终 1:新 增 2:编 辑
                // 'auto_type' => 'function' /* 自动完成方式 function:函数 field:字段 string:字符串 */

                $data['type'] = $v['type'];
                empty($v['remark']) || $data['remark'] = $v['remark'];
                empty($v['is_show']) || $data['is_show'] = $v['is_show'];
                empty($v['extra']) || $data['extra'] = $v['extra'];

                if (!empty($v['validate_rule'])) {
                    $data['validate_type'] = $v['validate_type'];
                    $data['validate_rule'] = $v['validate_rule'];
                    $data['validate_time'] = $v['validate_time'];
                    $data['error_info'] = $v['error_info'];
                }
                if (!empty($v['auto_rule'])) {
                    $data['auto_rule'] = $v['auto_rule'];
                    $data['auto_time'] = $v['auto_time'];
                    $data['auto_type'] = $v['auto_type'];
                }
                if (empty($data)) {
                    continue;
                }

                $newList[$name] = array_merge($info, $data);
            }

            foreach ($oldFields as $name => &$f) {
                if (isset($newList[$name])) {
                    $f = $newList[$name];
                    unset($newList[$name]);
                }
            }

            $dao->buildFile($vo, $oldFields);
            unset($vo);
        }
        // exit ();
        return $this->success('更新完成', U('index'));
    }

    public function getDBInfo($dbname, $fields = [], $type = 'filetodb')
    {
        // 检查表是否存在
        $table_exist = D('common/Models')->checkTableExist($dbname);
        if (!$table_exist) {
            return [];
        }

        $db_name = DB_PREFIX . strtolower($dbname);
        $sql = 'SHOW FULL COLUMNS FROM ' . $db_name;
        $fields_list = M()::query($sql);

        // 组装数据
        $data = [];
        foreach ($fields_list as $v) {
            $name = $v['Field'];
            $info = isset($fields[$name]) && $type != 'filetodb' ? $fields[$name] : [];
            // 主键不需要放到文件里
            if ($name == 'id' || ($dbname == 'user' && $name == 'uid') || ($dbname == 'tool' && $name == 'uid')) {
                continue;
            }

            if ($v['Null'] == 'YES') {
                $null = ' NULL';
                $is_must = isset($info['is_must']) ? $info['is_must'] : 0;
            } else {
                $null = ' NOT NULL';
                $is_must = 1;
            }

            $data[$name] = [
                'title' => empty($v['Comment']) && isset($info['title']) ? $info['title'] : $v['Comment'],
                'field' => $v['Type'] . $null,
                'type' => isset($info['type']) ? $info['type'] : 'string',
                'is_must' => $is_must
            ];
            // dump ( $v );
            // exit ();
            if ($v['Default'] != null) {
                $data[$name]['value'] = $v['Default'];
            }

            $data[$name] = array_merge($info, $data[$name]);
            if (empty($data[$name]['title'])) {
                $data[$name]['title'] = $name;
            }
            if ($data[$name]['is_must'] == 1 && (!isset($data[$name]['is_show']) || $data[$name]['is_show'] == 0)) {
                $data[$name]['is_must'] = 0;
            }

        }
        unset($fields_list);

        return $data;
    }

    public function importModel()
    {
        $model['name'] = I('model_name');
        $model['addon'] = I('model_addon');

        $dao = D('common/Models');
        $obj = $dao->getFileInfo($model);

        if ($obj == false) {
            return $this->error('获取模型文件出错');
        }

        // 增加到模型数据表
        $model_id = $dao->insertGetId($obj->config);
        // 字段增加
        if (!empty($obj->fields)) {
            foreach ($obj->fields as $name => $f) {
                $f['model_id'] = $model_id;
                $f['name'] = $name;

                $dao->addField($f);
            }
        }

        return $this->success('导入成功', U('index'));
    }

    public function freshFiletoDB()
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(0);
        }
        $dao = D('common/Models');
        $map = [];
        if (isset($_GET['model_id'])) {
            $map['id'] = I('model_id');
        }
        $list = find_data($dao->where($map)->select());
        foreach ($list as $vo) {
            $obj = $dao->getFileInfo($vo);
            if ($obj == false) {
                continue;
            }

            $data = $this->getDBInfo($vo['name']);

            // 字段比较
            foreach ($obj->fields as $name => $f) {
                $f['model_id'] = $vo['id'];
                $f['name'] = $name;
                if (isset($data[$name])) {
                    // 更新字段
                    $val = $data[$name];
                    if ($this->diffValue($val, $f, 'title') || $this->diffValue($val, $f, 'field') || $this->diffValue($val, $f, 'value') || $this->diffValue($val, $f, 'is_must')) {
                        isset($val['name']) || $val['name'] = $name;
                        $dao->updateField($f, $val);
                    }
                    unset($data[$name]);
                } else {
                    // 新增字段
                    $dao->addField($f);
                }
            }

            if (!empty($data)) {
                // 删除字段
                foreach ($data as $n => $field) {
                    $field['name'] = $name;
                    $dao->deleteField($field, $vo['id']);
                }
            }
        }

        return $this->success('更新完成', U('index'));
    }

    private function diffValue($old, $new, $field)
    {
        if (!isset($old[$field]) && !isset($new[$field])) {
            return false;
        } elseif (isset($old[$field]) && isset($new[$field]) && $old[$field] == $new[$field]) {
            return false;
        } else {
            return true;
        }
    }

    public function freshFileMd5()
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(0);
        }
        $dao = D('common/Models');
        $list = find_data($dao->select());
        foreach ($list as $vo) {
            $file = $dao->requireFile($vo);
            $save['file_md5'] = md5_file($file);
            $map['id'] = $vo['id'];
            $dao->where($map)->save($save);
        }

        return $this->success('更新完成', U('index'));
    }

    /**
     * 新增页面初始化
     *
     * @author huajie <banhuajie@163.com>
     */
    public function add()
    {
        // 获取所有的模型
        $models = find_data(M('model')->field('id,name,title')->select());
        $this->assign('models', $models);

        $this->_get_all_addon();
        $this->assign('inherit_list', $this->inherit_list());

        $this->meta_title = '新增模型';
        return $this->fetch();
    }

    public function copyModel()
    {
        if (IS_POST) {
            $post = input('post.');
            foreach ($post as $p) {
                if (empty($p)) {
                    return $this->error('参数不能为空');
                }
            }
            //复制模型文件
            $dao = D('common/Models');
            $obj = $dao->getFileInfo($post['id']);
            if ($obj === false) {
                return $this->error('模型不存在或已被删除');
            }
            $obj->config['name'] = $post['name'];
            $obj->config['title'] = $post['title'];
            $obj->config['addon'] = $post['addon'];
            $model = $obj->config;
            // 增加到模型数据表
            $model['id'] = $model_id = $dao->insertGetId($obj->config);
            // 字段增加
            if (!empty($obj->fields)) {
                foreach ($obj->fields as $name => $f) {
                    $f['model_id'] = $model_id;
                    $f['name'] = $name;

                    $dao->addField($f);
                }
            }

            $dao->buildFile($model, $obj->fields, $obj->list_grid, $obj->config);
            return $this->success('复制成功');

        } else {
            $this->_get_all_addon();
            return $this->fetch();
        }
    }

    private function inherit_list()
    {
        $inherit_list['common_category'] = '通用分类';
        return $inherit_list;
    }

    public function _get_all_addon()
    {
        $list = find_data(M('apps')->field('name,title')->select());

        $arr['core'] = '系统核心模块';
        foreach ($list as $vo) {
            $name = parse_name($vo['name']);
            $arr[$name] = $vo['title'];
        }
        $arr['api'] = 'Api应用';
        $arr['admin'] = 'Admin应用';
        $arr['home'] = 'Home应用';
        $arr['weiapp'] = 'Weiapp应用';
        $this->assign('list', $arr);

        return $arr;
    }

    /**
     * 编辑页面初始化
     *
     * @author huajie <banhuajie@163.com>
     */
    public function edit()
    {
        $id = I('id', '');
        if (empty($id)) {
            return $this->error('140156:参数不能为空！');
        }

        /* 获取一条记录的详细数据 */
        $data = M('model')->field(true)->where('id', $id)->find();
        if (!$data) {
            return $this->error('140157:获取模型数据失败');
        }
        // 更新前台缓存
        S('getModelByName_' . $data['name'], null);

        $obj = D('common/Models')->getFileInfo($data);
        $config = (array)$obj->config;
        $fields = (array)$obj->fields;

        $this->_get_all_addon();
        if (!isset($data['inherit']) || empty($data['inherit'])) {
            $data['inherit'] = '';
            $data['inherit_title'] = '无';
        } else {
            $inherit_list = $this->inherit_list();
            $data['inherit_title'] = isset($inherit_list[$data['inherit']]) ? $inherit_list[$data['inherit']] : '无';
        }
        $info = array_merge($data, $config);

        if ($data['need_pk']) {
            $fields['id'] = [
                'name' => 'id',
                'type' => 'Int',
                'is_must' => 0,
                'title' => 'ID主键'
            ];
        }
        if (isset($obj->list_grid['urls']['href'])) {
            foreach ($obj->list_grid['urls']['href'] as &$vo) {
                $vo['show'] = '';
                if (!empty($vo['class'])) {
                    $vo['show'] .= "({$vo['class']});";
                }
                if (isset($vo['show_set']) && !empty($vo['show_set'])) {
                    foreach ($vo['show_set'] as $n => $valArr) {
                        $title = $fields[$n]['title'];
                        $extra = parse_field_attr($fields[$n]['extra']);
                        $res = [];
                        foreach ($valArr as $v) {
                            $res[] = isset($extra[$v]) ? $extra[$v] : $v;
                        }
                        $val = implode(', ', $res);
                        $vo['show'] .= "{$title}=[$val];";
                    }
                }
            }
        }


        $this->assign('fields', $fields);
        $this->assign('list_grid', $obj->list_grid);
        $this->assign('info', $info);

        return $this->fetch();
    }

    /**
     * 删除一条数据
     *
     * @author huajie <banhuajie@163.com>
     */
    public function del()
    {
        $ids = I('ids');
        if ($ids < 0) {
            $files = session('file_scan');
            $ids = 0 - $ids;
            isset($files[$ids]) && @unlink($files[$ids]);
            $res = true;
        } else {
            if (empty($ids)) return $this->error('140158:参数不能为空！');
            $ids = explode(',', $ids);
            foreach ($ids as $value) {
                $res = D('common/Models')->del($value);
                if (!$res) {
                    break;
                }
            }
        }
        if (!$res) {
            return $this->error(D('common/Models')->getError());
        } else {
            return $this->success('删除模型成功！');
        }

    }

    /**
     * 更新一条数据
     *
     * @author huajie <banhuajie@163.com>
     */
    public function update()
    {
        $data = I('post.');

        $res = D('common/Models')->buildFileByData($data);
        // dump ( $res );
        // exit ();
        if (!$res) {
            return $this->error('140160:保存失败');
        } else {
            $name = parse_name($data['name']);

            $key = cache_key('name:' . $name, DB_PREFIX . 'model');
            S($key, null);

            !empty($data['addon']) && $data['addon'] != 'Core' && $res['id'] > 0 && $this->update_sql($data['addon']);
            return $this->success('保存成功', U('index'));
        }
    }

    /**
     * 生成一个模型
     *
     * @author huajie <banhuajie@163.com>
     */
    public function generate()
    {
        if (!request()->isPost()) {
            // 获取所有的数据表
            $tables = D('common/Models')->getTables();

            $this->assign('tables', $tables);
            $this->meta_title = '生成模型';
            return $this->fetch();
        } else {
            $table = I('post.table');
            if (empty($table)) return $this->error('请选择要生成的数据表！');
            $res = D('common/Models')->generate($table, I('post.name'), I('post.title'));
            if ($res) {
                return $this->success('生成模型成功！', U('index'));
            } else {
                return $this->error(D('common/Models')->getError());
            }
        }
    }

    /**
     * 导出一个模型
     */
    public function export($is_all = false, $model_id = 0, $export_type = 0, $return_sql = false)
    {
        $id = empty($model_id) ? I('id') : $model_id;
        $type = empty($export_type) ? I('type/d', 0) : $export_type;
        if (empty($id)) return $this->error('140163:参数不能为空！');
        $px = DB_PREFIX;

        // 模型信息
        $map['id'] = $id;
        $model = D('common/Models')->where($map)->find();

        // 模型字段
        $list = D('common/Models')->getFileInfo($model);
        if (empty($list)) {
            return '';
        }

        // 模型数据表
        $name = $model['name'];
        $data = [];
        $return_sql || $data = find_data(M(parse_name($name, true))->select());
        $name = strtolower($name);
        $sql = '';
        if ($type == 1) {
            $sql .= "DELETE FROM `{$px}model` WHERE `name`='{$model['name']}' ORDER BY id DESC LIMIT 1;\r\n";
            $sql .= "DROP TABLE IF EXISTS `{$px}" . strtolower($name) . "`;";
            $path = $is_all ? SITE_PATH . '/runtime/uninstall/' . $model['name'] . '.sql' : SITE_PATH . '/runtime/uninstall.sql';
        } else {
            // 获取索引表
            $index = '';
            $indexArr = [];
            try {
                $index_list = M()::query("SHOW INDEX FROM {$px}{$name}");
            } catch (\Exception $e) {
                //dump($e->getMessage());
                return '';
            }
            foreach ($index_list as $vo) {
                if ($vo['Key_name'] == 'PRIMARY') {
                    continue;
                }

                if (isset($indexArr[$vo['Key_name']])) {
                    $indexArr[$vo['Key_name']]['field'][] = $vo['Column_name'];
                } else {
                    $px_type = '';
                    if ($vo['Index_type'] == 'FULLTEXT') {
                        $px_type = 'FULLTEXT ';
                    } elseif ($vo['Non_unique'] == 0) {
                        $px_type = 'UNIQUE ';
                    }
                    $indexArr[$vo['Key_name']]['text'] = $px_type . 'KEY `' . $vo['Key_name'] . '`';
                    $indexArr[$vo['Key_name']]['field'][] = '`' . $vo['Column_name'] . '`';
                }
            }
            foreach ($indexArr as $vv) {
                $index .= $vv['text'] . ' (' . implode(',', $vv['field']) . "),\r\n";
            }
            $index = trim($index, ",\r\n");

            if ($model['need_pk']) {
                $create_table = "`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',\r\n";
                $key = empty($index) ? "PRIMARY KEY (`id`)" : "PRIMARY KEY (`id`),\r\n";
            }

            foreach ($list->fields as $n => $field) {
                // 获取默认值
                if ($field ['value'] === '') {
                    $default = '';
                } elseif (is_numeric($field ['value'])) {
                    $default = ' DEFAULT ' . $field ['value'];
                } elseif (is_string($field ['value'])) {
                    $default = ' DEFAULT \'' . $field ['value'] . '\'';
                } else {
                    $default = '';
                }
                $field['title'] = isset($field['title']) ? $field['title'] : '';
                $create_table .= "`{$n}`  {$field['field']} {$default} COMMENT '{$field['title']}',\r\n";
            }


            $sql .= <<<sql
CREATE TABLE IF NOT EXISTS `{$px}{$name}` (
{$create_table}{$key}{$index}
) ENGINE={$model['engine_type']} DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci CHECKSUM=0 ROW_FORMAT=DYNAMIC DELAY_KEY_WRITE=0;\r\n
sql;
            $search = array(
                "\r\n",
                "'"
            );
            $replace = array(
                '\r\n',
                "\\'"
            );
            unset($field);
            foreach ($data as $d) {
                $field = '';
                $value = '';
                foreach ($d as $k => $v) {
                    $field .= "`$k`,";
                    $value .= "'" . str_replace($search, $replace, $v) . "',";
                }
                $sql .= "INSERT INTO `" . $px . "{$name}` (" . rtrim($field, ',') . ') VALUES (' . rtrim($value, ',') . ");\r\n";
            }


            unset($model['id']);
            $field = '';
            $value = '';
            if (is_object($model)) {
                $model = $model->getData();
            }
            foreach ($model as $k => $v) {
                $field .= "`$k`,";
                $value .= "'" . str_replace($search, $replace, $v) . "',";
            }
            $sql .= 'INSERT INTO `' . $px . 'model` (' . rtrim($field, ',') . ') VALUES (' . rtrim($value, ',') . ');' . "\r\n";

            $path = $is_all ? SITE_PATH . '/runtime/install/' . $model['name'] . '.sql' : SITE_PATH . '/runtime/install.sql';
        }

        if ($return_sql) {
            return $sql;
        }

        if ($is_all) {
            mkdirs(SITE_PATH . '/runtime/install');
            mkdirs(SITE_PATH . '/runtime/uninstall');
        }

        @file_put_contents($path, $sql);

        if (!$is_all) {
            return redirect(SITE_URL . '/' . $path);
        }
    }

    // 一键增加应用应用常用模型
    public function add_comon_model()
    {
        $install_sql = SITE_PATH . '/app/admin/conf/common_model.sql';
        if (file_exists($install_sql)) {
            execute_sql_file($install_sql);
        }
        return $this->success('增加成功');
    }

    // 导出全部模型数据
    public function export_all()
    {
        $id = I('id/d', 0);
        $type = I('type/d', 0);
        $map['id'] = array(
            '>',
            $id
        );
        $info = M('model')->where(wp_where($map))
            ->order('id asc')
            ->find();
        if (!$info) {
            echo 'It is over';
            exit();
        }

        $this->export(true, $info['id'], 0);
        $this->export(true, $info['id'], 1);

        $param['id'] = $info['id'];

        echo 'export ' . $info['name'] . ' now...';

        $url = U('export_all', $param);
        echo '<script>window.location.href="' . $url . '"</script> ';
    }

    // 更新应用的安装卸载文件
    public function update_sql()
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(0);
        }

        $id = I('id/d', 0);
        $map['id'] = array(
            '>',
            $id
        );
        $addon = M('apps')->where(wp_where($map))->order('id asc')->find();
        if (!$addon) {
            return redirect(U('index'));
        }
        $map2['addon'] = $addon['name'];
        $addon_name = parse_name($addon['name']);
        $list = M('model')->where(wp_where($map2))->order('id asc')->select();

        if (!empty($list)) {
            $path = realpath(SITE_PATH . '/app/' . $addon_name);

            $install_sql = $uninstall_sql = '';
            foreach ($list as $info) {
                $install_sql .= $this->export(true, $info['id'], 0, true) . "\r\n" . "\r\n" . "\r\n";
                $uninstall_sql .= $this->export(true, $info['id'], 1, true) . "\r\n" . "\r\n" . "\r\n";
            }

            // 更新文件
            @file_put_contents($path . '/install.sql', $install_sql);
            @file_put_contents($path . '/uninstall.sql', $uninstall_sql);
        }
//         if (!empty($addon_name)) {
//             return true;
//         }

        $param['id'] = $addon['id'];
        echo 'update ' . $addon_name . ' now...';

        $url = U('update_sql', $param);
        echo '<script>window.location.href="' . $url . '"</script> ';
    }

    // 遍历所有的模型文件
    public function file_scan()
    {
        $fileArr = [];

        $path = SITE_PATH . DIRECTORY_SEPARATOR . 'app';
        $dirfat = dir($path);
        while (false !== $entry = $dirfat->read()) {
            if ($entry == '.' || $entry == '..' || $entry == '.svn' || is_file($path . DIRECTORY_SEPARATOR . $entry)) {
                continue;
            }
            if (!is_dir($path . DIRECTORY_SEPARATOR . $entry . DIRECTORY_SEPARATOR . 'data_table')) {
                continue;
            }
            $fileDir = dir($path . DIRECTORY_SEPARATOR . $entry . DIRECTORY_SEPARATOR . 'data_table');
            while (false !== $file = $fileDir->read()) {
                if ($file == '.' || $file == '..' || $file == '.svn') {
                    continue;
                }
                $fileArr[] = $file_name = $path . DIRECTORY_SEPARATOR . $entry . DIRECTORY_SEPARATOR . 'data_table' . DIRECTORY_SEPARATOR . $file;
            }
            $fileDir->close();
        }

        $dirfat->close();

        return $fileArr;
    }

    function phinx()
    {
        $this->assign('page_tips', '需要有mysql的information_schema数据库的访问权限，因此建议使用root账号访问。连接格式：数据库地址;端口号;数据库名称;用户名;密码<br/>
注意：需要手工调整的部分<br/>一、表或字段重命名无法通过对比数据库得知，程序只能当作删除了旧字段和增加了新字段两个操作，因此需要手工更正
<br/>二、表的字符集（如utf-8）更改无法通过phinx实现，因此也需要手工实现
<br/>三、表的主键更改需要手工实现');
        $db1 = db_config('phinx_db1');
        if (!$db1) {
            $config = config('database.connections.mysql');
            $db1 = $config['hostname'] . ';' . $config['hostport'] . ';' . $config['database'] . ';' . $config['username'] . ';' . $config['password'];
        }
        $this->assign('db1', $db1);
        $db2 = db_config('phinx_db2');
        $this->assign('db2', $db2);

        return $this->fetch();
    }

    function checkInput($num)
    {
        $db = input('db' . $num);
        if (empty($db)) return false;

        $arr = explode(';', $db);
        if (count($arr) != 5) return false;

        return true;
    }

    function mysqlDiff()
    {
        if (!$this->checkInput(1) || !$this->checkInput(2)) {
            echo -1;
        } else {
            echo D('common/Phinx')->mysqlDiff();
        }
    }

    function showSet()
    {
        $model_id = input('model_id');
        $model = $this->getModel($model_id);
        $obj = D('common/Models')->getFileInfo($model_id);
        $fields = get_model_attribute($model, $obj);
        if (IS_POST) {
            $post = input('post.');

            $show = [];
            if (isset($post['field'])) {
                foreach ($post['field'] as $f) {
                    isset($post[$f]) && $show[$f] = $post[$f];
                }
            }
            $msg = '';
            if (!empty($post['class_val'])) {
                $msg .= "({$post['class_val']});";
            }
            if (!empty($show)) {
                foreach ($show as $n => $valArr) {
                    $title = $fields[$n]['title'];
                    $extra = parse_field_attr($fields[$n]['extra']);
                    $res = [];
                    foreach ($valArr as $v) {
                        $res[] = isset($extra[$v]) ? $extra[$v] : $v;
                    }
                    $val = implode(', ', $res);
                    $msg .= "{$title}=[$val];";
                }
            }
            return $this->success($msg, null, urlencode(json_encode($show)));
        } else {
            $field = json_decode(urldecode(input('field', '[]')), true);
            empty($field) && $field = [];
            $this->assign('showSet', $field);
            foreach ($fields as $k => $v) {
                if (!in_array($v['type'], ['bool', 'radio', 'checkbox', 'select'])) {
                    unset($fields[$k]);
                }
            }
            $this->assign('fields', $fields);

            return $this->fetch();
        }
    }

}
