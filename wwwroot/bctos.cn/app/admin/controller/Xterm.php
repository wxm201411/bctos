<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星 <xw@bctos.cn> <QQ:203163051>
// +----------------------------------------------------------------------
namespace app\admin\controller;


class Xterm extends Admin
{
    function lists()
    {
        $model = $this->getModel();

        return $this->commonLists($model, '', 'count desc,id desc');
    }

    function xterm()
    {
        return $this->fetch('terminal');
    }
}
