<?php

namespace app\clock_in\controller;

use app\common\controller\ApiBase;


class Api extends ApiBase
{
    protected $apiModel;

    function initialize()
    {
        parent::initialize();
        $this->apiModel = D('ApiData');
    }

    function getUser()
    {
        //从GET或POST的请求中获取uid参数，/d 表示格式为数字，0 表示取不参数时的默认值
        $uid = input('uid/d', 0);
        if ($uid <= 0) {
            //uid必须大于0，返回错误提示，error第二个参数code不传，默认为1
            return $this->error('用户ID参数不正确');
        }

        //从数据库的user表中获取用户ID
        $user = M('user')->where('uid', $uid)->find();
        if ($user) {
            //正确返回用户数据
            return $this->success($user);
        } else {
            //获取用户失败，返回错误提示，code值为getUserEmpty，方便后续调试时知道哪里报的错误
            return $this->error('用户ID参数不正确', 'getUserEmpty');
        }
    }
}

