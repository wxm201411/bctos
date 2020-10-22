<?php

namespace app\blockchain\controller;

use app\common\controller\WebBase;

//PC运营管理端的控制器
class Channel extends WebBase
{
    function lists()
    {

        return $this->fetch();
    }
}
