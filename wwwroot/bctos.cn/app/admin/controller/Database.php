<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星 <xw@bctos.cn> <QQ:203163051>
// +----------------------------------------------------------------------
namespace app\admin\controller;


class Database extends Admin
{
    function lists()
    {
        $this->assign('page_tips', "添加数据库后，请务必到<a href='" . U('admin/cron/lists') . "'>[计划任务]</a>中添加数据库定时备份，以确保您的数据安全");

        $model = $this->getModel();
        $list_data = $this->getModelList($model);
        unset($list_data['list_grids']['ip']);

        foreach ($list_data['list_data'] as &$vo) {
            if (empty($vo['db_user'])) {
                $vo['db_user'] = $vo['db_name'];
            }
            if (empty($vo['backup_at'])) {
                $vo['backup_at'] = '未备份';
            }
            $vo['backup_at'] = "<a class='backup' rel='{$vo['id']}' title='{$vo['db_name']}'>{$vo['backup_at']}</a> ";
            $vo['urls'] = '<a class="root-pwd" rel="' . $vo['id'] . '">改密</a> | ' . $vo['urls'];
            if (empty($vo['db_passwd']) && empty($vo['remark'])) {
                $vo['remark'] = '	非本面板创建的数据库无法获取密码，可通过改密设置新密码';
            }
            if ($vo['power'] == 'IP') {
                $vo['power'] = "<a class='power-set' rel='{$vo['id']}' title='{$vo['db_user']}'>{$vo['ip']}</a> ";
            } else if (!empty($vo['power'])) {
                $vo['power'] = "<a class='power-set' rel='{$vo['id']}' title='{$vo['db_user']}'>{$vo['power']}</a> ";
            }


        }
        $this->assign($list_data);
        return $this->fetch();
    }

    function dockerCheck($mysql)
    {
        ssh_execute("[ -z $(docker ps --format '{{.Names}}'|grep $mysql) ] && $(cd /bctos/server/$mysql; docker-compose up -d; sleep 2)");
    }

    function pwd()
    {
        $id = input('id');
        $data = M('database')->where('id', $id)->find();
        if (IS_POST) {
            $old = input('old_pwd');
            $pwd = input('pwd');
            if ($old == $pwd) {
                return $this->success('修改成功');
            }
            $save = ['db_passwd' => $pwd];
            if (!$data['db_user']) {
                $save['db_user'] = $data['db_name'];
                $save['power'] = 'localhost';
                $res = ssh_execute(SITE_PATH . "/scripts/sys/addMysqlUser.sh {$data['database']} {$pwd} {$data['db_user']} localhost");
            } else {
                $res = ssh_execute(SITE_PATH . "/scripts/sys/editUserPwd.sh {$pwd} {$data['db_user']} {$data['database']}");
            }
            if ($res['code'] != 0) {
                return $this->error($res['msg']);
            }

            M('database')->where('id', $id)->update($save);
            //if (function_exists('opcache_reset')) opcache_reset();
            return $this->success('修改成功');
        } else {
            $this->assign('pwd', $data['db_passwd']);

            $this->assign('page_tips', "修改密码后请自行修改使用该账号连接数据库的网站");
            $this->dockerNotRun($data['database']);
            return $this->fetch();
        }
    }

    function power()
    {
        $id = input('id');
        $data = M('database')->where('id', $id)->find();
        if (IS_POST) {
            $power = input('power');
            $ip_input = input('ip');
            $ip_input = trim(trim(str_replace('，', ',', $ip_input), ','));
            if ($power == 'ip') {
                if ($data['power'] == $power && $data['ip'] == $ip_input) {
                    return $this->success('修改成功');
                }
                if (empty($ip_input)) {
                    return $this->success('IP地址不能为空');
                }
                $ipArr = array_unique(array_filter(explode(',', $ip_input)));
                $list = M()::query("SELECT Host FROM mysql.`user` where `User`='{$data['db_user']}' and `Host` like '%.%'");
                $has = [];
                foreach ($list as $vo) {
                    $has[$vo['Host']] = 1;
                }
                foreach ($ipArr as $ip) {
                    if (isset($has[$ip])) {
                        unset($has[$ip]);//已经存在，不需要理会
                    } else {
                        //需要新增加账号
                        $res = ssh_execute(SITE_PATH . "/scripts/sys/addMysqlUser.sh {$data['database']} {$data['db_passwd']} {$data['db_user']} {$ip}");
                        if ($res['code'] != 0) {
                            return $this->error($res['msg']);
                        } else {
                            unset($has[$ip]);
                        }
                    }
                }
                if (!empty($has)) {
                    //需要删除多余账号
                    foreach ($has as $ip => $v) {
                        $res = ssh_execute(SITE_PATH . "/scripts/sys/delMysqlUser.sh {$data['db_user']} {$ip} {$data['database']}");
                        if ($res['code'] != 0) {
                            return $this->error($res['msg']);
                        }
                    }
                }
            } else {
                if ($data['power'] == $power) {
                    return $this->success('修改成功');
                }

                $list = M()::query("SELECT `Host` FROM mysql.`user` where `User`='{$data['db_user']}' and `Host`='$power'");
                if (count($list) == 0) {
                    //数据库已经存在，只需要修改本地数据库即可,只有数据库没有该权限的账号，才需要下面的更新操作
                    $res = ssh_execute(SITE_PATH . "/scripts/sys/editMysqlPower.sh {$data['power']} {$power} {$data['db_user']} {$data['database']}");
                    if ($res['code'] != 0) {
                        return $this->error($res['msg']);
                    }
                }
            }

            M('database')->where('id', $id)->update(['power' => $power, 'ip' => $ip_input]);
            return $this->success('修改成功');
        } else {
            $model = $this->getModel();
            $fields = get_model_attribute($model);

            empty($data['power']) && $data['power'] = 'localhost';

            $this->assign('field', $fields['power']);
            $this->assign('field2', $fields['ip']);
            $this->assign('data', $data);

            $this->dockerNotRun($data['database']);

            return $this->fetch();
        }
    }

    function add()
    {
        $model = $this->getModel();
        if (request()->isPost()) {
            $Model = D($model['name']);
            // 获取模型的字段信息
            $post = input('post.');
            $post = $this->checkData($post, $model);
            $res = $Model->addDb($post);
            if ($res['code'] == 0) {
                return $this->success('添加数据库成功！');
            } else {
                return $this->error($res['msg']);
            }
        } else {
            $fields = get_model_attribute($model);
            $data = default_form_value($fields);
            $post_url = U('add?model=' . $model['id'], $GLOBALS['get_param']);

            $data['db_passwd'] = uniqid();

            $this->assign('fields', $fields);
            $this->assign('data', $data);
            $this->assign('post_url', $post_url);

            $ssh = ssh_execute("docker ps --format '{{.Names}}' | egrep 'mysql'");
            $arr = wp_explode($ssh['msg']);
            $dbs = [];
            foreach ($arr as $v) {
                if (empty($v)) continue;

                $dbs[] = $v;
            }

            $this->assign('dbs', $dbs);

            return $this->fetch();
        }
    }

    function del()
    {
        $model = $this->getModel();

        $id = I('id/d');
        if (empty($id)) return $this->error('非法操作!');

        $Model = D($model['name']);

        if (false !== $Model->delDb($id)) {
            return $this->success('删除成功');
        } else {
            return $this->error('删除失败！');
        }
    }


    function sycServer()
    {
        $res = D('Database')->getTablesFromDocker();
        if ($res['code'] != 0) {
            return $this->error($res['msg']);
        }
        return $this->success($res['msg']);
    }
}