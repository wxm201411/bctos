<?php

// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星
// +----------------------------------------------------------------------
namespace app\admin\controller;

/**
 * 模型数据管理控制器
 *
 * @author 凡星
 */
class Menu extends Admin
{

    /**
     * 显示指定模型列表数据
     *
     * @param String $model
     *          模型标识
     * @author 凡星
     */
    public function lists()
    {
        $gid = input('gid/d', 2);
        session('menu_gid', $gid);
        $groups = M('auth_group')->where('type', 0)->column('title', 'id');
        $nav = [];
        foreach ($groups as $id => $g) {
            $res ['title'] = $g;
            $res ['url'] = U('lists?gid=' . $id);
            $res ['class'] = $gid == $id ? 'current' : '';
            $nav [] = $res;
        }
        $this->assign('nav', $nav);

        $model = $this->getModel('menu');
        $list_data = $this->getModelList($model, 'sort asc,id asc', true);

        $list_data ['list_data'] = $this->treeList($list_data ['list_data'], 0, '', $gid);
        $list_data ['_page'] = '';

        $this->assign($list_data);

        return $this->fetch('lists');
    }

    public function admin_lists()
    {
        return $this->lists();
    }

    function treeList($data, $pid = 0, $level = '', $gid = 0)
    {
        $tree = [];
        $child_level = empty($level) ? '├──' : $level . '───';
        foreach ($data as $vo) {
            if ($pid == $vo['pid']) {
                $rule = wp_explode($vo['rule']);
                if ($gid == 0 || in_array($gid, $rule)) {
                    $vo['title'] = $level . '<a style="color:#1E9FFF" href="' . U('edit?id=' . $vo['id']) . '">' . $vo['title'] . '</a>';
                    if ($level != '├─────') {
                        $url = U('add', [
                            'pid' => $vo ['id']
                        ]);
                        $vo ['urls'] = '<a target="_self" href="' . $url . '">增加子菜单</a>&nbsp;&nbsp;&nbsp;' . $vo ['urls'];
                    }

                    $tree[] = $vo;
                    //查找下级
                    $child = $this->treeList($data, $vo['id'], $child_level, $gid);
                    if (!empty($child)) {
                        $tree = array_merge($tree, $child);
                    }
                }
            }
        }
        return $tree;
    }

    public function _get_data($map, $list_data)
    {
        $hask = [];
        foreach ($list_data as $vo) {
            $hask [$vo ['id']] = $vo ['urls'];
        }

        $list = find_data(M('menu')->field(true)->where(wp_where($map))->order('sort asc,id asc')->select());
        $model = $this->getModel('Menu');
        $list = $this->parseListData($list, $model);
        // lastsql ();
        // 取一级菜单
        $one_arr = $data = [];
        $place = I('place', '');
        foreach ($list as $k => $vo) {
            if ($vo ['pid'] != 0) {
                continue;
            }

            $url = U('add', [
                'place' => $place,
                'pid' => $vo ['id']
            ]);
            $hh = isset($hask [$vo ['id']]) ? $hask [$vo ['id']] : '';
            $vo ['urls'] = '<a target="_self" href="' . $url . '">增加子菜单</a>&nbsp;&nbsp;&nbsp;' . $hh;
            $one_arr [$vo ['id']] = $vo;
            unset($list [$k]);
        }

        foreach ($one_arr as $p) {
            $data [] = $p;

            $two_arr = array();
            foreach ($list as $key => $l) {
                if ($l ['pid'] != $p ['id']) {
                    continue;
                }

                $l ['title'] = '├──' . $l ['title'];
                $l ['urls'] = isset($hask [$l ['id']]) ? $hask [$l ['id']] : '';
                $two_arr [] = $l;
                unset($list [$key]);
            }

            $data = array_merge($data, $two_arr);
        }

        return $data;
    }

    public function edit()
    {
        $id = I('id');
        $model = $this->getModel('menu');

        // 获取数据
        $data = M('menu')->find($id);
        if (empty($data)) return $this->error('140151:数据不存在！');

        if (request()->isPost()) {
            $Model = D($model ['name']);
            $data = input('post.');
            $data = $this->_check_data($data);
            // 获取模型的字段信息
            $data = $this->checkData($data, $model);
            $res = D('common/Menu')->updateMenuData($data, ['id' => $id]);

            if ($res !== false) {
                return $this->success('保存成功！', U('lists?gid=' . session('menu_gid')));
            } else {
                return $this->error($Model->getError());
            }
        } else {
            $this->getFiedls($model ['id'], $data);
            $data['rule'] = D('AuthGroup')->getRuleByMenuId($data['id']);

            return $this->fetch();
        }
    }

    public function add()
    {
        $model = $this->getModel('menu');

        if (request()->isPost()) {
            $data = input('post.');
            $data = $this->_check_data($data);
            $Model = D($model ['name']);
            // 获取模型的字段信息
            $data = $this->checkData($data, $model);

            $id = D('common/Menu')->addData($data);
            if ($id) {
                return $this->success('添加成功！', U('lists?gid=' . session('menu_gid')));
            } else {
                return $this->error($Model->getError());
            }
        } else {
            $this->getFiedls($model ['id']);

            $post_url = U('add');
            $this->assign('post_url', $post_url);

            return $this->fetch();
        }
    }

    private function getFiedls($model_id, $data = [])
    {
        $fields = get_model_attribute($model_id);
        $place = session('admin_place');
        $fields ['pid'] ['extra'] = str_replace('[place]', $place, $fields ['pid'] ['extra']);

        $hasPid = input('pid/d', 0);
        if ($hasPid > 0) {
            $fields['pid']['value'] = $hasPid;
        }
        $this->assign('fields', $fields);
        empty($data) && $data = default_form_value($fields);
        $this->assign('data', $data);
    }

    public function del()
    {

        $ids = I('id');
        !empty($ids) || $ids = array_filter(array_unique((array)I('ids', 0)));

        D('common/Menu')->delData($ids);
        return $this->success('删除成功');
    }

    private function _check_data($data)
    {
        if (!isset($data ['url_type']) || $data ['url_type'] == 0) {
            $data ['url'] = '';
        } else {
            $data ['addon_name'] = '';
        }
        $data['pid'] = intval($data['pid']);
        return $data;
    }
}
