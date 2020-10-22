<?php

namespace app\clock_in\model;

use app\common\model\ServiceBase;

//应用对外提供服务的接口
class Service extends ServiceBase
{
    function CronEndEvent()
    {
        $data['nowTime'] = (string)NOW_TIME;
        up_chain('CronEndEvent', $data);

    }

    function UserClockIn($uid, $event_id, $keyword)
    {
        $data['userKey'] = 'user_' . $uid;
        $data['eventKey'] = 'event_' . $event_id;
        $data['keyword'] = (string)$keyword;
        $data['nowTime'] = (string)NOW_TIME;
        up_chain('UserClockIn', $data);
    }

    function UserJoinEvent($uid, $event_id, $payType = '0')
    {
        $data['userKey'] = 'user_' . $uid;
        $data['eventKey'] = 'event_' . $event_id;
        $data['payType'] = (string)$payType;
        up_chain('UserJoinEvent', $data);
    }

    function EventReg($event_id)
    {
        $event = M('clock_in')->where('id', $event_id)->find();
        $obj = D('common/Models')->getFileInfo('clock_in');

        $moneyArr = parse_field_attr($obj->fields['money_select']['extra']);
        $prizeTypeArr = parse_field_attr($obj->fields['prize_type']['extra']);
        $statusArr = parse_field_attr($obj->fields['status']['extra']);
        //dump($moneyArr);

        $event['money_select'] = isset($moneyArr[$event['money_select']]) ? $moneyArr[$event['money_select']] : $event['money_select'];
        $event['money_int'] = $event['money_select'] == '免费' ? 0 : intval($event['money_select']);
        $event['prize_type'] = isset($prizeTypeArr[$event['prize_type']]) ? $prizeTypeArr[$event['prize_type']] : $event['prize_type'];

        $PrizeCount = intval(str_replace('前', '', $prizeTypeArr[$event['prize_type']]));

        $keys = M('clock_in_keyword')->where('event_id', $event['id'])->select();
        $Eventkeywords = [];
        foreach ($keys as $k) {
            $Eventkeywords[$k['keyword']] = [$k['start_time'], $k['end_time']];
        }
        $data['eventKey'] = 'event_' . $event['id'];
        $data['userKey'] = 'user_' . $event['uid'];
        $data['PrizeMoney'] = (string)$event['prize_money'];
        $data['PrizeCount'] = (string)$PrizeCount;
        $data['JoinMoney'] = (string)$event['money_int'];
        $data['Eventkeywords'] = json_encode($Eventkeywords);
        $data['StartTime'] = (string)$event['start_time'];
        $data['EndTime'] = (string)$event['end_time'];
        $data['IsEnd'] = $event['end_time'] < NOW_TIME ? '1' : '0';

        $config = getAppConfig('clock_in');
        $ratio = explode(':', $config['ratio'], 3);

        $data['AdminPercent'] = (string)$ratio[0];
        $data['AnchorPercent'] = (string)$ratio[1];
        up_chain('EventReg', $data);
    }

    function UserReg($uid)
    {
        $data['userKey'] = 'user_' . $uid;
        up_chain('UserReg', $data);
    }

    function GetValue($key)
    {
        $data['key'] = (string)$key;
        up_chain('GetValue', $data);
    }

    function SetValue($key, $value, $canUpdate = '1')
    {
        $data['key'] = (string)$key;
        $data['value'] = (string)$value;
        $data['canUpdate'] = (string)$canUpdate;
        up_chain('SetValue', $data);
    }
}
