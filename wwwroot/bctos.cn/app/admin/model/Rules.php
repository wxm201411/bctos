<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ouyangessen
// +----------------------------------------------------------------------

namespace app\admin\model;
use app\common\model\Base;

/**
 * 权限管理模型
 */
class Rules extends Base{

    public function getRoleList(){

    }

    public function getTagInfo($id = null){
        $id || $id = input('id');
        $map = array(
            'id' => $id
        );
        $tag = M( 'auth_group' )->where( wp_where($map) )->find();

        return $tag;
    }

}
