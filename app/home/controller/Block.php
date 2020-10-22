<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星 <xw@bctos.cn> <QQ:203163051>
// +----------------------------------------------------------------------
namespace app\home\controller;

use QL\QueryList;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class Block extends Home
{
    function saveData($recovery = false)
    {
        $post = input('post.');

        $data['page'] = $post['page'];
        $data['block_id'] = $post['block_id'];
        unset($post['page'], $post['block_id']);

        if ($recovery) {
            foreach ($post as &$vo) {
                $vo = '';
            }
        }

        if ($data['block_id'] == -1) {
            foreach ($post as $key => $value) {
                $data['block_id'] = $key;
                $content = json_encode($value);
                break;
            }
        } else {
            $content = json_encode($post);
        }

        $id = M('block')->where(wp_where($data))->value('id');


        if ($id > 0) {
            M('block')->where('id', $id)->update(['content' => $content]);
        } else {
            $data['content'] = $content;
            M('block')->insert($data);
        }


        return $this->success('保存成功');
    }
    function recovery()
    {
        return $this->saveData(true);
    }
}
