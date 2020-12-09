<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星 <xw@bctos.cn> <QQ:203163051>
// +----------------------------------------------------------------------
namespace app\admin\controller;


class SiteBackup extends Admin
{
    function lists()
    {
        $site_id = input('site_id/d', 0);
        $type = input('type/d', 0);
        $this->assign('top_more_button', [['title' => '备份', 'url' => U('backup', $GLOBALS['get_param']), 'class' => 'ajax-backup']]);

        $GLOBALS['where']['site_id'] = $site_id;
        $GLOBALS['where']['type'] = $type;

        return parent::lists();
    }

    private function getFilePath($data)
    {
        $cron_id = M('cron')->where('type', $data['type'])->where('site_id', $data['site_id'])->value('id');
        if ($cron_id > 0 && file_exists(SITE_PATH . '/public/storage/backup/' . $cron_id . '/' . $data['title'])) {
            return $cron_id . '/' . $data['title'];
        } else {
            return '0/' . $data['title'];
        }
    }

    function download()
    {
        $id = I('id');
        $data = M('site_backup')->where('id', $id)->find();

        return redirect(SITE_URL . '/storage/backup/' . $this->getFilePath($data));
    }

    function del()
    {
        $model = $this->getModel();
        $id = I('id');
        $Model = M($model['name']);
        $data = $Model->where('id', $id)->find();

        if ($Model->where('id', $id)->delete()) {
            @unlink(SITE_PATH . '/public/storage/backup/' . $this->getFilePath($data));
            $at = $Model->where('site_id', $data['site_id'])->where('type', $data['type'])->order('backup_at desc')->value('backup_at');
            if ($data['type'] == 1) {
                M('site')->where('id', $data['site_id'])->update(['backup_at' => intval($at)]);
            } else {
                M('database')->where('id', $data['site_id'])->update(['backup_at' => intval($at)]);
            }

            return $this->success('删除成功');
        } else {
            return $this->error('删除失败！');
        }
    }

    function recovery()
    {
        set_time_limit(0);

        $id = I('id');
        $data = M('site_backup')->where('id', $id)->find();
        $file = $this->getFilePath($data);
        if ($data['type'] == 0) {
            $database = M('database')->where('id', $data['site_id'])->find();
            $file = substr($file, 0, -4);
            list($cron_id, $fileName) = explode('/', $file);
            $res = ssh_execute(SITE_PATH . "/scripts/sys/recoveryDatabase.sh {$database['db_name']} {$fileName} {$cron_id} {$database['database']}");
        } else {
            $path = M('site')->where('id', $data['site_id'])->value('path');
            $res = ssh_execute(SITE_PATH . "/scripts/sys/recoverySite.sh $file {$path}");
        }
        if ($res['code'] == 1) {
            return $this->error($res['msg']);
        } else {
            if (function_exists('opcache_reset')) opcache_reset();
            return $this->success('恢复成功');
        }
    }

    function backup()
    {
        $site_id = input('site_id/d', 0);
        $type = input('type/d', 0);
        $cron = M('cron')->where('type', $type)->where('site_id', $site_id)->find();
        if ($cron) {
            $cron_id = $cron['id'];
            $max = $cron['max_keep'];
        } else {
            $cron_id = 0;
            $max = 1000;
        }

        if ($type != 0) {
            $db = 'site';
            $data = M($db)->where('id', $site_id)->find();
            $recode['title'] = $data['title'] . '.zip';
            $res = ssh_execute(SITE_PATH . "/scripts/cron/cronBackupSite.sh {$data['path']} {$recode['title']} {$max} '\*/node_modules/\* \*/runtime/\* \*.log' {$cron_id}");
            if ($res['code'] == 1) {
                return $this->error($res['msg']);
            }
            $recode['type'] = 1;
        } else {
            $db = 'database';
            $data = M($db)->where('id', $site_id)->find();

            $file = $data['db_name'] . '.sql';
            $res = ssh_execute(SITE_PATH . "/scripts/cron/cronBackupDatabase.sh {$data['db_name']} {$file} {$max} {$data['database']} {$cron_id}");
            if ($res['code'] == 1) {
                return $this->error($res['msg']);
            }
            $recode['type'] = 0;
        }
        ssh_execute("echo \"{$res['msg']}\" >> /bctos/logs/cron_{$cron_id}.log");

        if ($cron_id == 0) {
            $arr = wp_explode($res['msg'], "\n");
            $recode['title'] = $size = '';
            foreach ($arr as $k => $vo) {
                if (strpos($vo, '：') === false) continue;
                list($t, $v) = explode('：', $vo);
                if (strpos($t, '压缩包大小') !== false) {
                    $size = trim($v);
                } elseif (strpos($t, '已备份到') !== false) {
                    $arr2 = explode('/', trim($v));
                    $recode['title'] = array_pop($arr2);
                }
            }
            $recode['file_size'] = $size;
            $recode['backup_at'] = NOW_TIME;
            $recode['site_id'] = $site_id;
            M('site_backup')->insert($recode);
            M($db)->where('id', $site_id)->update(['backup_at' => NOW_TIME]);
        }

        return $this->success('保存成功');
    }
}
