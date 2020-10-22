<?php

namespace app\common\controller;

//PC运营管理端的控制器
class CategoryBase extends WebBase
{
    var $model;
    var $app;

    function initialize()
    {
        parent::initialize();
        $this->model = $this->getModel('cms_category');
        $this->app = MODULE_NAME;
    }

    function lists()
    {
        $model = $this->model;
        //dump($model);
        $this->assign('model', $this->model);

        $GLOBALS['where_forbit_field'] = 'id';

        $dataTable = D('common/Models')->getFileInfo($model);

        $this->assign('add_button', $dataTable->config['add_button']);
        $this->assign('del_button', $dataTable->config['del_button']);
        $this->assign('search_button', $dataTable->config['search_button']);
        $this->assign('check_all', $dataTable->config['check_all']);

        // 解析列表规则
        $list_data = $this->listGrid($model, $dataTable);

        // 搜索条件
        $GLOBALS['where']['app'] = $this->app;
        $map = $this->searchMap($model, $list_data['db_fields']);

        // 读取模型数据列表
        // dump ( $order );
        $name = $dataTable->config['name'];

        //扩展字段
        $meta_table = D('common/Models')->getFileInfo($model);
        $data = D('common/Category')->getList($meta_table, $map);

        $id = id();
        if ($id > 0) {
            // 获取数据
            $info = [];
            foreach ($data as $d) {
                if ($d['id'] == $id) {
                    $info = $d;
                    break;
                }
            }
            if (empty($info)) return $this->error('数据不存在！');

            $this->assign('data', $info);
            $this->assign('submit_name', '保 存');
        } else {
            $this->assign('data', default_form_value($dataTable->fields));
        }

        $data = treeList($data);
        $list_data['list_data'] = $this->parseListData($data, $dataTable);

        $this->assign($list_data);

        $this->assign('fields', $meta_table->fields);

        return $this->fetch('category');
    }

    public function edit()
    {
        $id = id();
        if (!request()->isPost()) {
            return $this->redirect('lists?id=' . $id);
        }

        $model = $this->model;

        // 获取模型的字段信息
        $data = input('post.');
        //只取最后一个ID
        if (!empty($data['pid'])) {
            $pids = array_filter(explode(',', $data['pid']));
            $data['pid'] = intval(array_pop($pids));
        }
        $data = $this->checkData($data, $model);
        $data['app'] = $this->app;
        $res = D('common/Category')->updateInfo($model, $data, $id);
        if ($res !== false) {
            return $this->success('保存成功！', U('lists?model=' . $model['name'], $this->get_param));
        } else {
            return $this->error('保存失败');
        }
    }

    public function del()
    {
        $ids = array_filter(array_unique((array)input('id', 0)));
        !empty($ids) || $ids = array_filter(array_unique((array)input('ids', 0)));
        if (empty($ids)) return $this->error('请选择要删除的数据!');

        // 同时要删除下级分类
        $child_ids = $this->getMultChildIds($ids);
        if (!empty($child_ids)) {
            $ids = array_merge($ids, $child_ids);
        }

        if (M('common_category')->whereIn('id', $ids)->delete()) {
            // 删除关联数据
            M('common_category_meta')->whereIn('category_id', $ids)->delete();
            M('common_category_link')->whereIn('category_id', $ids)->delete();

            return $this->success('删除成功');
        } else {
            return $this->error('删除失败！');
        }
    }

    public function getMultChildIds($ids)
    {
        $child_ids = M('common_category')->whereIn('pid', $ids)->column('id');
        if (!empty($child_ids)) {
            $level_ids = $this->getMultChildIds($child_ids);
            $child_ids = array_merge($child_ids, $level_ids);
        } else {
            return [];
        }
        return $child_ids;
    }
}
