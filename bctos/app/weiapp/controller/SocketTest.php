<?php
namespace app\weiapp\controller;
use app\common\controller\BaseController;

class SocketTest extends BaseController
{
    public function index()
    {
        D('Sms/Sms')->sendSms($mobile);
    }
}
