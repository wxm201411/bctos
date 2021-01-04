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
class Database extends Base
{
    protected static $con;

    function connectDb($name)
    {
        $res = ssh_execute('cat /bctos/server/' . $name . '/docker-compose.yml');
        $yml = yaml_parse($res['msg']);

        $config = config('database');
        $db = ['type' => 'mysql', 'charset' => 'utf8mb4', 'prefix' => '', 'fields_strict' => false, 'hostname' => $name,
            'hostport' => 3306,
            'database' => 'mysql',
            'username' => 'root',
            'password' => $yml['services'][$name]['environment']['MYSQL_ROOT_PASSWORD']];

        $config['connections'][$name] = $db;
        config($config, 'database');
        $this->con = \think\facade\Db::connect($name);
    }

    function getTablesFromDocker()
    {
        $ssh = ssh_execute("docker ps --format '{{.Names}}'|grep mysql");
        if ($res['code'] != 0 || empty($ssh['msg'])) {
            $this->error('无法找到运行的mysql容器');
        }

        $dbs = $userArr = $has = [];

        $hasList = $this->field('db_name,database')->select();
        foreach ($hasList as $vo) {
            $has[$vo['database']][] = $vo['db_name'];
        }

        $dockers = wp_explode($ssh['msg']);
        foreach ($dockers as $d) {
            $this->connectDb($d);

            $sql = "show databases";
            $list = $this->con->query($sql);

            $sys = ['information_schema', 'mysql', 'performance_schema', 'sys', 'bctos_cn'];

            isset($has[$d]) && $sys = array_merge($sys, $has[$d]);

            foreach ($list as $k => $vo) {
                if (!in_array($vo['Database'], $sys)) {
                    $dbs[$d][] = $vo['Database'];
                }
            }
            if (isset($dbs[$d]) && !empty($dbs[$d])) {
                $sql = "select Db,Host,User from mysql.db where Db in ('" . implode("', '", $dbs[$d]) . "') order by Host asc";
                $users = $this->con->query($sql);
                foreach ($users as $u) {
                    if (!isset($userArr[$d][$u['Db']])) {
                        $userArr[$d][$u['Db']] = $u;
                    }
                }
            }
        }
        if (empty($dbs)) {
            return $this->error('没有新的数据库');
        }

        $all = [];
        foreach ($dbs as $docker => $dbArr) {
            foreach ($dbArr as $db_name) {
                $data['db_name'] = $db_name;
                $data['database'] = $docker;
                $data['db_user'] = '';
                $data['db_passwd'] = '';
                $data['power'] = '';
                $data['ip'] = '';
                if (isset($userArr[$docker]) && isset($userArr[$docker][$db_name])) {
                    $data['db_user'] = $userArr[$docker][$db_name]['User'];
                    $h = $userArr[$docker][$db_name]['Host'];
                    if ($h == '%') {
                        $data['power'] = '%';
                    } elseif ($h == 'localhost' || $h == '127.0.0.1') {
                        $data['power'] = 'localhost';
                    } else {
                        $data['power'] = 'ip';
                        $data['ip'] = $h;
                    }
                }

                $all[] = $data;
            }
        }
        $this->insertAll($all);
        return $this->success('共获取了' . count($all) . '个数据库');
    }

    function addDb($post)
    {
        $this->connectDb($post['database']);

        if (empty($post['db_name'])) {
            return $this->error('请先填写数据库名称');
        }
        if (strpos($post['db_name'], '.') !== false) {
            return $this->error('数据库名不能存在点号');
        }
        if (empty($post['db_user'])) {
            return $this->error('请先填写用户名');
        }
        if (empty($post['db_passwd'])) {
            return $this->error('请先填写密码');
        }
        if ($post['power'] == 'ip' && empty($post['ip'])) {
            return $this->error('请先填写IP地址');
        }

        $sql = "SHOW DATABASES LIKE '{$post['db_name']}'";
        $res = $this->con->query($sql);
        if (count($res)) {
            return $this->error('该数据库已经存在，请换名再试');
        }

        $sql = "SELECT Host FROM mysql.`user` where `User`='{$post['db_user']}'";
        $res = $this->con->query($sql);
        if (count($res)) {
            return $this->error('该用户名已经存在，请换名再试');
        }

        //增加数据库及用户
        $sqlArr[] = "CREATE DATABASE {$post['db_name']} DEFAULT CHARACTER SET {$post['db_set']};";
        if ($post['power'] != 'ip') {
            $sqlArr[] = "CREATE USER '{$post['db_user']}'@'{$post['power']}' IDENTIFIED WITH mysql_native_password BY '{$post['db_passwd']}';";
            $sqlArr[] = "GRANT ALL ON {$post['db_name']}.* TO '{$post['db_user']}'@'{$post['power']}';";
        } else {
            $ipArr = array_unique(array_filter(explode(',', $post['ip'])));
            foreach ($ipArr as $ip) {
                //需要新增加账号
                $sqlArr[] = "CREATE USER '{$post['db_user']}'@'$ip' IDENTIFIED WITH mysql_native_password BY '{$post['db_passwd']}';";
                $sqlArr[] = "GRANT ALL ON {$post['db_name']}.* TO '{$post['db_user']}'@'$ip';";
            }
        }
        $sqlArr[] = "flush privileges;";
        foreach ($sqlArr as $sql) {
            $this->con->execute($sql);
        }
        $id = $this->insertGetId($post);
        //自动增加定时任务
        D('Cron')->addCronAuto(0, $id, $post['db_name'], $post['database']);

        return $this->success($id);
    }

    function dockerCheck($mysql)
    {
        ssh_execute("[ -z $(docker ps --format '{{.Names}}'|grep $mysql) ] && $(cd /bctos/server/$mysql; docker-compose up -d; sleep 2)");
    }

    function delDb($id, $info = [])
    {
        empty($info) && $info = $this->where('id', $id)->find();
        if (!$info) {
            return $this->success('数据库记录不存在');
        }
        $this->dockerCheck($info['database']);
        $this->connectDb($info['database']);

        $res = $this->con->execute('DROP DATABASE IF EXISTS ' . $info['db_name']);
        $list = $this->con->query("SELECT `Host`,`User` FROM mysql.`db` where Db='{$info['db_name']}'");
        foreach ($list as $vo) {
            if ($vo['User'] != 'root' && $vo['User'] != 'mysql.session' && $vo['User'] != 'mysql.sys') {
                $this->con->execute("DROP USER '{$vo['User']}'@'{$vo['Host']}'");
            }
        }
        $this->con->execute('flush privileges');

        $this->where('id', $id)->delete();
        //自动删除定时任务
        D('Cron')->delCronAuto($info['id'], 0);
        return $res;
    }

    function delDbByName($database, $db_name)
    {
        $info = $this->where('database', $database)->where('db_name', $db_name)->find();
        if (!$info) {
            return $this->success('数据库记录不存在');
        }
        return $this->delDb($info['id'], $info);
    }
}
