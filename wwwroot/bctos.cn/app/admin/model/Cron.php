<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星 <xw@bctos.cn> <QQ:203163051>
// +----------------------------------------------------------------------
namespace app\admin\model;

use app\common\model\Base;

/**
 * 日志模型
 */
class Cron extends Base
{
    function addCronAuto($type, $site_id, $title, $path = '')
    {
        $post['type'] = $type;
        $post['title'] = $title;
        $post['path'] = $path;
        $post['site_id'] = $post['database_id'] = $site_id;
        $post['database'] = $path . ',' . $site_id;
        $post['time_minute'] = rand(0, 59);
        $post['time_cron'] = $post['time_minute'] . ' 3 * * *';
        $post['time_type'] = 'day';
        $post['time_day'] = $post['time_day2'] = 1;
        $post['time_hour'] = 3;
        $post['time_week'] = 0;
        $post['max_keep'] = 3;
        $post['exclude'] = $type == 0 ? '' : '\*/node_modules/\* \*/runtime/\* \*.log';
        return $this->addCron($post);
    }

    function addCron($post)
    {
        $type = $post['type'];
        if ($post['max_keep'] < 1 && in_array($type, [0, 1, 3, 6])) {
            return $this->error("保留最新备份的数量不能少于1");
        } else if ($type == 0 && empty($post['database'])) {
            return $this->error("请选择备份的数据库");
        } else if ($type == 2 && (empty($post['title']) || empty($post['shell_content']))) {
            return $this->error("脚本名称或脚本内容不能为空");
        } else if ($type == 5 && empty($post['jump_url'])) {
            return $this->error("URL地址不能为空");
        } else if ($type == 6 && empty($post['dir_path'])) {
            return $this->error("备份目录不能为空");
        } else if ($post['time_type'] == 'custom') {
            if (empty($post['time_cron'])) {
                return $this->error("请填写自定义执行周期");
            }
            $arr = wp_explode($post['time_cron']);
            if (count($arr) < 5) {
                return $this->error("自定义执行周期格式不正确");
            }
        }
        $check = 0;
        $map = ['type' => $type, 'site_id' => $post['site_id']];
        if ($type == 0) {
            list($post['database'], $post['database_id']) = explode(',', $post['database'], 2);
            $map['database'] = $post['database'];
            $map['database_id'] = $post['database_id'];
        }
        $check = M('cron')->where($map)->value('id');
        if ($check > 0) {
            return $this->error("该任务已存在，请勿重复增加");
        }
        $post['time_cron'] = $this->cronTime($post);
        $x = $post['exclude'];
        switch ($type) {
            case 0:
                $post['site_id'] = $post['database_id'];
                if ($post['database_id'] == '0') {
                    $post['title'] = "备份{$post['database']}的所有数据库";
                    $file = 'allDbs.sql';
                    $comand = "cronBackupDatabase.sh - {$file} {$post['max_keep']} {$post['database']}";
                } else {
                    $database = M('database')->where('id', $post['database_id'])->find();
                    $file = $database['db_name'] . '.sql';
                    $post['title'] = "备份{$post['database']}的数据库-{$database['db_name']}";
                    $comand = "cronBackupDatabase.sh {$database['db_name']} {$file} {$post['max_keep']} {$database['database']}";
                }
                break;
            case 1:
                if ($post['site_id'] == '0') {
                    $post['title'] = '备份所有网站';
                    $file = 'allSite.zip';
                    $comand = "cronBackupSite.sh - {$file} {$post['max_keep']} '$x'";
                } else {
                    $site = M('site')->where('id', $post['site_id'])->find();
                    $file = $site['title'] . '.zip';
                    $post['title'] = '备份网站-' . $site['title'];
                    $comand = "cronBackupSite.sh {$site['path']} {$file} {$post['max_keep']} '$x'";
                }
                break;
            case 2:
                isset($post['id']) || $post['title'] = 'Shell脚本-' . $post['title'];
                break;
            case 5:
                $post['title'] = '访问:' . $post['jump_url'];
                $comand = 'cronUrl.sh ' . $post['jump_url'];
                break;
            case 6:
                $post['title'] = '备份目录-' . $post['dir_path'];
                $comand = "cronBackupDir.sh {$post['dir_path']} {$post['max_keep']} '$x'";
                break;
            case 7:
                $post['title'] = '同步时间';
                $comand = 'cronTime.sh';
                break;
        }
        if (isset($post['id']) && $post['id'] > 0) {
            $res = M('cron')->where('id', $post['id'])->update($post);
            $id = $post['id'];
            ssh_execute('sed -i "/\/bctos\/logs\/cron_' . $id . '/d" /var/spool/cron/root');
        } else {
            $id = M('cron')->insertGetId($post);
        }
        if ($type == 2) {
            $file = SITE_PATH . '/data/custom_shell/' . $id . '.sh';
            file_put_contents($file, $post['shell_content']);
            $comand = 'cronShell.sh ';
        }

        $log = "/bctos/logs/cron_{$id}.log 2>&1";
        $comand = "sed -i '/^[  ]*$/d' /var/spool/cron/root;echo \"\n{$post['time_cron']}  /bctos/wwwroot/bctos.cn/scripts/cron/{$comand} $id >> {$log}\" >> /var/spool/cron/root";
        $res = ssh_execute($comand);
        if ($res['code'] == 1) {
            return $this->error($res['msg']);
        }

        return $this->success('保存任务成功');
    }

    function delCronAuto($site_id, $type)
    {
        $info = $this->where('site_id', $site_id)->where('type', $type)->find();
        if (!$info) {
            return $this->success('不存在定时任务');
        }

        return $this->del($info['id']);
    }

    function del($id)
    {
        if (empty($id)) return $this->error('非法操作!');

        $res = ssh_execute(SITE_PATH . "/scripts/cron/cronDel.sh $id");
        if ($res['code'] == 1) {
            return $this->error($res['msg']);
        }
        if (false !== M('cron')->where('id', $id)->delete()) {
            return $this->success('删除成功');
        } else {
            return $this->error('删除失败！');
        }
    }

    function cronTime($vo)
    {
        $time = "* * * * *";
        switch ($vo['time_type']) {
            case 'day';
                $time = "{$vo['time_minute']} {$vo['time_hour']} * * *";
                break;
            case 'day-n';
                $time = "{$vo['time_minute']} {$vo['time_hour']} */{$vo['time_day2']} * *";
                break;
            case 'hour';
                $time = "{$vo['time_minute']} * * * *";
                break;
            case 'hour-n';
                $time = "{$vo['time_minute']} */{$vo['time_hour']} * * *";
                break;
            case 'minute-n';
                $time = "*/{$vo['time_minute']} * * * *";
                break;
            case 'week';
                $time = "{$vo['time_minute']} {$vo['time_hour']} * * {$vo['time_week']}";
                break;
            case 'month';
                $time = "{$vo['time_minute']} {$vo['time_hour']} {$vo['time_day']} * *";
                break;
            case 'custom';
                $time = $vo['time_cron'];
                break;
        }
        return $time;
    }
}
