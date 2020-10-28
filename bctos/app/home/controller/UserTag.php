<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星 <xw@bctos.cn> <QQ:203163051>
// +----------------------------------------------------------------------
namespace app\home\controller;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class UserTag extends Home
{

    public $model = '';

    public function initialize()
    {
        parent::initialize();
        $this->model = $this->getModel('auth_group');
        // dump($this->model);
    }

    // 通用插件的列表模型
    public function lists()
    {
        $map['pbid'] = get_pbid();
        session('common_condition', $map);
        $this->assign('search_url', U('lists', array(
            'mdm' => isset($_GET['mdm']) ? input('mdm') : ''
        )));
        return parent::commonLists($this->model, 'lists');
    }

    // 通用插件的编辑模型
    public function edit()
    {
        $this->checkPost();
        return parent::commonEdit($this->model, 0, 'common@base/edit');
    }

    // 通用插件的增加模型
    public function add()
    {
        $this->checkPost();
        return parent::commonAdd($this->model, 'common@base/add');
    }

    function checkPost()
    {
        if (! IS_POST) {
            return false;
        }
        
        $title = input('title');
        $id = id();
        $count = D('common/UserTag')->where('title', $title)
            ->where('wpid', WPID)
            ->where('id', '<>', $id)
            ->count();
        if ($count > 0) {
            return $this->error('该标签已存在');
        }
    }

    // 通用插件的删除模型
    public function del()
    {
        return parent::commonDel($this->model);
    }
}
