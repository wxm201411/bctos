<?php

namespace app\clock_in\controller;

use app\common\controller\WebBase;

//PC运营管理端的控制器
class ClockIn extends WebBase
{
    //定时触发活动
    function CronEndEvent()
    {
        $data['nowTime'] = (string)NOW_TIME;
        up_chain('CronEndEvent', $data);

    }

    //用户打卡
    function UserClockIn()
    {
        $data['userKey'] = 'user_4567';
        $data['eventKey'] = 'event_7';
        $data['keyword'] = '111';
        $data['nowTime'] = (string)NOW_TIME;
        up_chain('UserClockIn', $data);
    }

    //用户参与活动
    function UserJoinEvent()
    {
        $data['userKey'] = 'user_123';
        $data['eventKey'] = 'event_7';
        $data['payType'] = '0';
        up_chain('UserJoinEvent', $data);
    }

    //新增活动
    function EventReg()
    {
        $data['eventKey'] = 'event_7';
        $data['userKey'] = 'user_123';
        $data['PrizeMoney'] = '1000';
        $data['PrizeCount'] = '100';
        $data['JoinMoney'] = '100';
        $data['Eventkeywords'] = json_encode(['111' => ['123', '456']]);
        $data['StartTime'] = '123';
        $data['EndTime'] = '456';
        $data['IsEnd'] = '0';
        $data['AdminPercent'] = '10';
        $data['AnchorPercent'] = '20';
        up_chain('EventReg', $data);
    }

    //新增用户
    function UserReg()
    {
        $data['userKey'] = 'user_456';
        up_chain('UserReg', $data);
    }

    //获取key值
    function GetValue()
    {
        $data['key'] = 'test_xiaowei';
        up_chain('GetValue', $data, 1);
    }

    //设置key值
    function SetValue()
    {
        $data['key'] = 'test_xiaowei';
        $data['value'] = 'hi xiaowei';
        $data['canUpdate'] = '1';
        up_chain('SetValue', $data);
    }
}