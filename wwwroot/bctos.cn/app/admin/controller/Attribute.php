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
 * 属性控制器
 *
 * @author huajie <banhuajie@163.com>
 */
class Attribute extends Admin
{

    /**
     * 属性列表
     *
     * @author huajie <banhuajie@163.com>
     */
    public function index()
    {
        $app_name = input('app_name');
        if ($app_name) {
            session('app_name', $app_name);
            $this->assign('model_title', '应用配置字段管理');
            $this->assign('model_id', -1);

            $list = get_app_config_file($app_name);
        } else {
            $model_id = I('model_id');
            $this->assign('model_id', $model_id);

            $obj = D('common/Models')->getFileInfo($model_id);
            $this->assign('model_title', $obj->config['title']);

            $list = $obj->fields;
        }

        foreach ($list as $k => &$vo) {
            $vo['show'] = '';
            if (isset($vo['show_set']) && !empty($vo['show_set'])) {
                foreach ($vo['show_set'] as $n => $valArr) {
                    $title = $list[$n]['title'];
                    $extra = parse_field_attr($list[$n]['extra']);
                    $res = [];
                    foreach ($valArr as $v) {
                        $res[] = isset($extra[$v]) ? $extra[$v] : $v;
                    }
                    $val = implode(', ', $res);
                    $vo['show'] .= "{$title}=[$val];";
                }
            }
        }
        $this->assign('list_data', $list);
        $this->assign('field_lists', D('common/Models')->fieldData(null));

        return $this->fetch();
    }

    function showSet()
    {
        $field = input('field');

        $model_id = input('model_id');
        if ($model_id != -1) {
            $model = $this->getModel($model_id);
            $obj = D('common/Models')->getFileInfo($model_id);
            $list = get_model_attribute($model, $obj);
        } else {
            $app_name = session('app_name');
            $list = $old = get_app_config_file($app_name);
        }

        if (IS_POST) {
            $post = input('post.');
            $dao = D('common/Models');

            $show = [];
            foreach ($post['field'] as $f) {
                isset($post[$f]) && $show[$f] = $post[$f];
            }
            $list[$field]['show_set'] = $show;
            if ($model_id != -1) {
                $dao->buildFile($model, $list, null, null);
            } else {
                $dao->buildConfigFile($app_name, $list, $old);
            }
            return $this->success('保存成功');
        } else {
            $this->assign('showSet', isset($list[$field]['show_set']) ? $list[$field]['show_set'] : []);
            unset($list[$field]);
            foreach ($list as $k => $v) {
                if (!in_array($v['type'], ['bool', 'radio', 'checkbox', 'select'])) {
                    unset($list[$k]);
                }
            }
            $this->assign('fields', $list);

            return $this->fetch();
        }
    }

    /**
     * 新增页面初始化
     *
     * @author huajie <banhuajie@163.com>
     */
    public function add()
    {
        $model_id = I('model_id');
        $model = M('model')->field('id,title,name')->find($model_id);

        $this->assign('model', $model);

        $this->assign('info', array(
            'model_id' => $model_id
        ));
        $this->meta_title = '新增属性';
        return $this->fetch('edit');
    }

    /**
     * 编辑页面初始化
     *
     * @author huajie <banhuajie@163.com>
     */
    public function edit()
    {
        $dao = D('common/Models');
        $name = I('name', '');
        $model_id = I('model_id', '');
        if (empty($name)) {
            return $this->error('140065:参数不能为空！');
        }

        /* 获取一条记录的详细数据 */
        if ($model_id != -1) {
            $obj = $dao->getFileInfo($model_id);
            $list = $obj->fields;
        } else {
            $app_name = session('app_name');
            $list = $old = get_app_config_file($app_name);
        }


        // 虚拟一个ID值，用于编辑
        $i = 1;
        foreach ($list as &$vo) {
            $vo['id'] = $i;
            $vo['model_id'] = $model_id;
            $i++;
        }

        if (!isset($list[$name])) {
            return $this->error('140066:参数不能正确！');
        }
        $list[$name]['name'] = $name;

        $model = $dao->field('id,title,name')->find($model_id);
        // dump ( $model );
        $this->assign('model', $model);
        $this->assign('info', $list[$name]);
        $this->meta_title = '编辑属性';
        return $this->fetch();
    }

    function save_sort()
    {
        $dao = D('common/Models');
        $data = I('post.');

        $model_id = $data['model_id'];
        $model_id = $data['model_id'];
        if ($model_id == -1) {
            $app_name = session('app_name');
            $list = $old = get_app_config_file($app_name);
        } else {
            $obj = $dao->getFileInfo($model_id);
            $list = $obj->fields;
        }

        $newList = [];
        foreach ($data['sort'] as $name) {
            $newList[$name] = $list[$name];
        }

        if ($model_id == -1) {
            $dao->buildConfigFile($app_name, $newList, $old);
        } else {
            $model = $dao->field(true)->find($model_id);
            $dao->buildFile($model, $newList);
        }

        return $this->success('保存成功');
    }

    /**
     * 更新一条数据
     *
     * @author huajie <banhuajie@163.com>
     */
    public function update()
    {
        $dao = D('common/Models');
        /* 获取数据对象 */
        $data = I('post.');

        if (isset($data['name']) && $data['name'] == 'file') {
            return $this->error('字段名不能为file，请换别的名称再试');
        }

        $model_id = $data['model_id'];
        if ($model_id == -1) {
            $app_name = session('app_name');
            $list = $old = get_app_config_file($app_name);
        } else {
            $obj = $dao->getFileInfo($model_id);

            if (!is_writable($obj->datatable_path)) {
                return $this->error('140071:' . $obj->datatable_path . '文件没有可写权限！');
            }
            $list = $obj->fields;
            $list_grid = $obj->list_grid;
        }


        if ($data['is_must'] == 1 && strpos($data['field'], 'NOT NULL') === false) {
            $data['field'] = str_replace('NULL', 'NOT NULL', $data['field']);
        } elseif ($data['is_must'] == 0 && strpos($data['field'], 'NOT NULL') !== false) {
            $data['field'] = str_replace('NOT NULL', 'NULL', $data['field']);
        }

        if (empty($data['id'])) { // 新增属性
            if ($model_id != -1 && (!isset($obj->config['inherit']) || $obj->config['inherit'] != 'common_category')) { //继承分类基类的表不需要创建数据表
                $res = $dao->addField($data);
                if (!$res) {
                    return $this->error('140067:新建字段出错！');
                }
            }

            $name = $data['name'];
            unset($data['id'], $data['name'], $data['model_id']);
            $list[$name] = $data;
            $newList = $list;
        } else { // 更新数据
            $i = 1;
            $old = $listArr = [];
            $id = $data['id'];
            foreach ($list as $name => $vo) {
                if ($id == $i) {
                    $old = $vo;
                    $old['name'] = $name;
                }

                $vo['name'] = $name;
                $listArr[$i] = $vo;
                $i++;
            }
            // dump ( $listArr );
            if ($model_id != -1 && (!isset($obj->config['inherit']) || $obj->config['inherit'] != 'common_category')) { //继承分类基类的表不需要创建数据表
                $res = $dao->updateField($data, $old);
                if (!$res) {
                    return $this->error('140068:更新字段出错！');
                }
            }

            //同步更新文件里的列表定义，并保持排序位置不变
            if ($model_id != -1 && isset($list_grid[$old['name']])) {
                $head = $footer = $now = [];
                $has = false;
                foreach ($list_grid as $k => $v) {
                    if ($k == $old['name']) {
                        $has = true;
                        $now[$data['name']] = $v;
                    } else {
                        if ($has) {
                            $footer[$k] = $v;
                        } else {
                            $head[$k] = $v;
                        }
                    }
                }
                $list_grid = array_merge($head, $now, $footer);
            }

            // 更新文件里的字段，并保持排序位置不变
            unset($list[$old['name']]);
            unset($data['id'], $data['model_id']);
            $listArr[$id] = $data;
            foreach ($listArr as $v) {
                $n = $v['name'];
                unset($v['name']);
                $newList[$n] = $v;
            }
        }
        // 删除字段缓存文件
        // dump ( $data );
        if ($model_id != -1) {
            $model = $dao->field(true)->find($model_id);
            $cache_name = config('database.connections.mysql.database') . '.' . preg_replace('/\W+|\_+/', '', $model['name']);
            S($cache_name, null, DATA_PATH . '_fields/');
            // dump ( $newList );
            // exit ();
            $dao->buildFile($model, $newList, $list_grid);
            $url = U('index', ['model_id' => $model_id]);
        } else {
            $dao->buildConfigFile($app_name, $newList, $old);
            $url = U('index', ['app_name' => $app_name]);
        }
        // 记录行为
        action_log('update_attribute', 'attribute', '', get_mid());

        return $this->success('保存成功', $url);
    }

    /**
     * 删除一条数据
     *
     * @author huajie <banhuajie@163.com>
     */
    public function remove()
    {
        $dao = D('common/Models');
        $model_id = I('model_id');
        $name = I('name');
        if ($model_id == -1) {
            $app_name = session('app_name');
            $list = $old = get_app_config_file($app_name);

            if (isset($list[$name])) unset($list[$name]);

            $dao->buildConfigFile($app_name, $list, $old);
            $url = U('index', ['app_name' => $app_name]);

            return $this->success('删除成功', $url);
        }

        $obj = $dao->getFileInfo($model_id);
        $list = $obj->fields;
        $grid = $obj->list_grid;


        if (!isset($list[$name])) return $this->error('140069:该字段不存在！');

        $info = $list[$name];
        $info['name'] = $name;

        // 更新数据模型文件
        unset($list[$name]);

        if (isset($grid[$name])) unset($grid[$name]);
        $dao->buildFile($model_id, $list, $grid);

        // 删除表字段
        $res = $dao->deleteField($info, $model_id);
        if (!$res) {
            return $this->error('140070:删除失败');
        } else {
            // 记录行为
            action_log('delete_attribute', $obj->config['name'] . ':' . $name, '', get_mid());
            return $this->success('删除成功', U('index', 'model_id=' . $model_id));
        }
    }

    public function add_fields()
    {
        $fields = input('fields');
        $dao = D('common/Models');

        $model_id = input('model_id');
        if ($model_id != -1) {
            $obj = $dao->getFileInfo($model_id);

            if (!is_writable($obj->datatable_path)) {
                return $this->error('140071:' . $obj->datatable_path . '文件没有可写权限！');
            }
            $list = $obj->fields;
            $list_grid = $obj->list_grid;
        } else {
            $app_name = session('app_name');
            $list = $old = get_app_config_file($app_name);
        }

        foreach ($fields as $f) {
            //已经存在相同的字段，直接忽略
            if (isset($list[$f])) continue;

            $data = $dao->fieldData($f);
            if (empty($data)) continue;

            $data['model_id'] = $model_id;
            if ($data['is_must'] == 1 && strpos($data['field'], 'NOT NULL') === false) {
                $data['field'] = str_replace('NULL', 'NOT NULL', $data['field']);
            } elseif ($data['is_must'] == 0 && strpos($data['field'], 'NOT NULL') !== false) {
                $data['field'] = str_replace('NOT NULL', 'NULL', $data['field']);
            }

            // 新增属性
            if ($model_id != -1 && (!isset($obj->config['inherit']) || $obj->config['inherit'] != 'common_category')) { //继承分类基类的表不需要创建数据表
                $res = $dao->addField($data);
            }

            $name = $data['name'];
            unset($data['id'], $data['name'], $data['model_id']);
            $list[$name] = $data;
        }
        if ($model_id != -1) {
            $model = $dao->field(true)->find($model_id);
            $cache_name = config('database.connections.mysql.database') . '.' . preg_replace('/\W+|\_+/', '', $model['name']);
            S($cache_name, null, DATA_PATH . '_fields/');
            // dump ( $newList );
            // exit ();
            $dao->buildFile($model, $list, $list_grid);
            $url = U('index', ['model_id' => $model_id, 't' => NOW_TIME]);
        } else {
            $dao->buildConfigFile($app_name, $list, $old);
            $url = U('index', ['app_name' => $app_name, 't' => NOW_TIME]);
        }
        return $this->success('保存成功', $url);
    }
}
