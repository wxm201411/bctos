<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星 <xw@bctos.cn> <QQ:203163051>
// +----------------------------------------------------------------------
namespace app\admin\controller;


class Cron extends Admin
{
    function lists()
    {
        $model = $this->getModel();
        $list_data = $this->getModelList($model, $order = 'id desc', $all_field = true);

        foreach ($list_data['list_data'] as &$vo) {
            //dump($vo);
            switch ($vo['time_type_db']) {
                case 'day';
                    $vo['time_type'] = "每天{$vo['time_hour']}点{$vo['time_minute']}分执行";
                    break;
                case 'day-n';
                    $vo['time_type'] = "每隔{$vo['time_day']}天的{$vo['time_hour']}点{$vo['time_minute']}分执行";
                    break;
                case 'hour';
                    $vo['time_type'] = "每小时的{$vo['time_minute']}分执行";
                    break;
                case 'hour-n';
                    $vo['time_type'] = "每隔{$vo['time_hour']}小时的{$vo['time_minute']}分执行";
                    break;
                case 'minute';
                    $vo['time_type'] = "每分钟都执行";
                    break;
                case 'minute-n';
                    $vo['time_type'] = "每隔{$vo['time_minute']}分钟执行";
                    break;
                case 'week';
                    $weekArr = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
                    $vo['time_type'] = "每星期的{$weekArr[$vo['time_week']]}{$vo['time_hour']}点{$vo['time_minute']}分执行";
                    break;
                case 'month';
                    $vo['time_type'] = "每月的{$vo['time_day']}号{$vo['time_hour']}点{$vo['time_minute']}分执行";
                    break;
            }
            if (!in_array($vo['type'], [0, 1, 3, 6])) {
                $vo['max_keep'] = '';
            }
        }
        $this->assign($list_data);
        $this->formParam();

        return $this->add();
    }

    function formParam()
    {
        $site_list = M('site')->field('id,title')->select();
        $this->assign('site_list', $site_list);

        $database_list = M('database')->field('id,db_name,database')->select();

        $extra = 'type=text&data=[';
        $arr = [];
        foreach ($database_list as $d) {
            if (isset($arr[$d['database'] . ':' . $d['database']])) {
                $arr[$d['database'] . ':' . $d['database']] .= $d['id'] . ':' . $d['db_name'] . ',';
            } else {
                $arr[$d['database'] . ':' . $d['database']] = '0:全部数据库,' . $d['id'] . ':' . $d['db_name'] . ',';
            }
        }
        foreach ($arr as $k => $v) {
            $v = trim($v, ',');
            $extra .= "{$k}[{$v}],";
        }
        $extra = trim($extra, ',') . "]";
        //dump($extra);
        //[1:广西[3:南宁,4:桂林],5:123[6:456,7:789,asd], 2:广东[广州, 深圳[福田区, 龙岗区[板田,龙华], 宝安区]]]
        $this->assign('extra', $extra);
    }

    function edit()
    {
        if (IS_GET) {
            $this->formParam();
        }
        return parent::edit();
    }

    function addCon()
    {
        $post = input('post.');
        $res = D('Cron')->addCron($post);
        if ($res['code'] == 1) {
            return $this->error($res['msg']);
        }
        return $this->success($res['msg']);
    }

    function del()
    {
        $id = I('id/d');
        $res = D('Cron')->del($id);
        if ($res['code'] == 1) {
            return $this->error($res['msg']);
        }
        return $this->success($res['msg']);
    }

    function delLog()
    {
        $id = I('id/d');
        if (empty($id)) return $this->error('非法操作!');

        $res = ssh_execute(":>/bctos/logs/cron_{$id}.log");
        if ($res['code'] == 1) {
            return $this->error($res['msg']);
        }
        return $this->success('日志清空成功');
    }

    function execute()
    {
        $id = I('id/d');
        if (empty($id)) return $this->error('非法操作!');

        $res = ssh_execute(SITE_PATH . "/scripts/cron/cronExecNow.sh $id");
        if ($res['code'] == 1) {
            return $this->error($res['msg']);
        }
        return $this->success('执行成功');
    }

    function log()
    {
        $id = I('id/d');
        if (empty($id)) return $this->error('非法操作!');

        $res = ssh_execute(SITE_PATH . "/scripts/cron/cronLog.sh $id");
        $this->assign('content', nl2br($res['msg']));
        return $this->fetch();
    }
}