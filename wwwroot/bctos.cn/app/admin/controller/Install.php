<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星 <xw@bctos.cn> <QQ:203163051>
// +----------------------------------------------------------------------
namespace app\admin\controller;


class Install extends Admin
{
    function install()
    {
        set_time_limit(0);
        $id = id();

        $domain = trim(str_replace(['https://', 'http:.//'], '', input('domain')), '/');
        //判断站点是否存在
        $check = M('site')->where('title', $domain)->value('id');
        if ($check > 0 || $domain == 'bctos.cn' || $domain == 'stop') {
            $this->error("该网站已存在，请删除后再试", 10);
        }

        $data = M('install')->where('id', $id)->find();
        $conf = $domain . '.conf';
        $rewrite_file = SITE_PATH . "/runtime/{$domain}.rewrite.conf";
        if ($data['rewrite_mod'] != '') {
            file_put_contents($rewrite_file, $data['rewrite']);
        }

        if ($data['php_version'] == 'not') {
            $phpConf = <<<str
#    location ~ \.php$ {
#        fastcgi_pass   {$data['php_version']}:9000;
#        fastcgi_index  index.php;
#        fastcgi_param  SCRIPT_FILENAME  \$document_root\$fastcgi_script_name;
#        include        fastcgi_params;
#    }
str;

        } else {
            $phpConf = <<<str
    location ~ \.php$ {
        fastcgi_pass   {$data['php_version']}:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME  \$document_root\$fastcgi_script_name;
        include        fastcgi_params;
    }
str;
        }

        //运行目录
        $data['public_path'] = trim(trim($data['public_path'], '/'));
        $public_path = '';
        if (!empty($data['public_path'])) {
            $public_path = '/' . $data['public_path'];
        }
        //入口文件
        $index = 'index.html index.php';
        if (!empty($data['index_file'])) {
            $index = $data['index_file'];
        }
        $path = "/bctos/wwwroot/{$domain}";
        //增加nginx配置文件
        $content = <<<str
server {
    listen 80;
    server_name  {$domain};
    root   {$path}{$public_path};
    index  {$index};

    #伪静态规则
    include /etc/nginx/rewrite/{$domain}.rewrite.conf;
    
{$phpConf}

    location ~ /\.ht {
        deny  all;
    }
    location ~ \.well-known{
        allow all;
    }
    location ~ .*\.(gif|jpg|jpeg|png|bmp|swf)$
    {
        expires      30d;
        error_log off;
        access_log /dev/null;
    }
    location ~ .*\.(js|css)?$
    {
        expires      12h;
        error_log off;
        access_log /dev/null; 
    }
    access_log  /bctos/logs/{$domain}.log;
    error_log  /bctos/logs/{$domain}.error.log;    
}
str;
        file_put_contents(SITE_PATH . '/runtime/' . $conf, $content);

        $db_pwd = uniqid();
        //增加数据库记录
        $db_name = str_replace('.', '_', $domain);
        $db = ['database' => $data['database'], 'db_name' => $db_name, 'db_user' => $db_name, 'db_passwd' => $db_pwd, 'db_set' => $data['db_set']];
        $site = ['domain' => $domain, 'path' => $path, 'php_version' => $data['php_version'], 'title' => $domain, 'public_path' => $data['public_path'], 'rewrite_mod' => $data['rewrite_mod'], 'create_at' => NOW_TIME];
        $site_id = M('site')->insertGetId(array_merge($db, $site));
        if ($data['database'] != 'not') {
            $db['power'] = '%';
            $database_id = M('database')->insertGetId($db);
            //自动增加定时任务
            D('Cron')->addCronAuto(0, $database_id, $db_name, $data['database']);
        }
        D('Cron')->addCronAuto(1, $site_id, $site['title'], $site['path']);

        // dump($data);exit;
        M('install')->where('id', $id)->update(['status' => 1]);

        foreach ($data as &$vo) {
            if (empty($vo)) {
                $vo = '-';
            }
        }
        $param = "{$data['database']} {$data['redis']} {$data['memcached']} {$data['php_version']} {$data['php_func']} {$data['php_ext']} ";
        $param .= "$domain {$data['download_type']} {$data['download_url']} {$data['db_file']} {$data['redis_file']} {$data['memcached_file']} {$data['rm_file']} {$data['db_set']} {$db_pwd}";
        ssh_execute_msg(SITE_PATH . "/scripts/sys/quickInstallSite.sh {$param}");

        return $this->success('');
    }

    function install_log()
    {
        $domain = trim(str_replace(['https://', 'http:.//'], '', input('domain')), '/');
        //判断站点是否存在
        if ($domain == 'bctos.cn' || $domain == 'stop') {
            $this->assign('msg', "该名称面板已经占用，请换名再试");
            return $this->fetch('xterm_error');
        }
        $check = M('site')->where('title', $domain)->value('id');
        if ($check > 0) {
            $this->assign('msg', "该网站已存在，请删除后再试");
            return $this->fetch('xterm_error');
        }

        $url = U('install', ['id' => input('id'), 'domain' => $domain]);
        $this->assign('url', $url);
        return $this->fetch('xterm_box');
    }

    function test()
    {
        $this->assign('url', '');
        return $this->fetch('xterm_box');
    }

    function audit()
    {
        $id = id();
        $is_audit = input('is_audit/d', 1);
        M('install')->where('id', $id)->update(['is_audit' => $is_audit]);
        return $this->success('成功');
    }

    function detail()
    {
        $id = id();
        $data = M('install')->where('id', $id)->find();
        $model = $this->getModel('install');
        $dataTable = D('common/Models')->getFileInfo($model);
        $data = $this->parseModelData($data, $dataTable);
        $this->assign('data', $data);
        return $this->fetch();
    }

    //aaabbbccc
    function add()
    {
        $model = $this->getModel();
        if (request()->isPost()) {
            // 获取模型的字段信息
            $data = input('post.');

            if (empty($data['title'])) {
                return $this->error('软件名称不能为空');
            }
            if (empty($data['download_url'])) {
                return $this->error('源码下载命令不能为空');
            }
            $data = $this->checkData($data, $model);
            $dao = M('install');
            if ($dao->where('title', $data['title'])->where('is_del', 0)->value('id')) {
                return $this->error('保存失败，该软件名称已经存在');
            }
            //$data['is_audit'] = 1;
            $id = $dao->insertGetId($data);
            if ($id) {
                return $this->success('保存成功', U('lists'));
            } else {
                return $this->error('保存失败，请通知管理员处理');
            }
        } else {
            $fields = get_model_attribute($model);
            $data = default_form_value($fields);
            $post_url = U('add?model=' . $model['id'], $GLOBALS['get_param']);

            $this->assign('fields', $fields);
            $this->assign('data', $data);
            $this->assign('post_url', $post_url);
            $this->assign('db_passwd', uniqid());
            // dump($fields);

            $ssh = ssh_execute("ls /bctos/server");

            $arr = wp_explode($ssh['msg']);
            $server = ['mysql_list' => [], 'redis_list' => [], 'php_list' => [], 'memcacheed_list' => []];
            foreach ($arr as $v) {
                if (empty($v)) continue;

                $str = substr($v, 0, 5);
                if ($str == 'mysql') {
                    $server['mysql_list'][] = $v;
                } elseif ($str == 'redis') {
                    $server['redis_list'][] = $v;
                } elseif (substr($v, 0, 3) == 'php') {
                    $server['php_list'][] = $v;
                } elseif (substr($v, 0, 9) == 'memcached') {
                    $server['memcached_list'][] = $v;
                }
            }
            $this->assign($server);
            $funcs = "exec,passthru,system,chroot,chgrp,chown,proc_open,pcntl_exec,ini_alter,ini_restore,dl,openlog,syslog,readlink,symlink,popepassthru,pcntl_waitpid,pcntl_wifexited,pcntl_wifstopped,pcntl_wifsignaled,pcntl_wifcontinued,pcntl_wexitstatus,pcntl_wtermsig,pcntl_wstopsig,pcntl_get_last_error,pcntl_strerror,pcntl_sigprocmask,pcntl_sigwaitinfo,pcntl_sigtimedwait,pcntl_exec,pcntl_getpriority,pcntl_setpriority,imap_open,apache_setenv";
            $this->assign('php_funcs', array_unique(explode(',', $funcs)));

            $mods = ['wordpress', 'thinkphp', 'laravel5', 'EduSoho', 'EmpireCMS', 'dabr', 'dbshop', 'dedecms', 'default', 'discuz', 'discuzx', 'discuzx2', 'discuzx3', 'drupal', 'ecshop', 'emlog', 'maccms', 'mvc', 'niushop', 'phpcms', 'phpwind', 'sablog', 'seacms', 'shopex', 'typecho', 'typecho2', 'wp2', 'zblog',];
            $this->assign('mods', $mods);

            return $this->fetch();
        }
    }

    function phpExt()
    {
        $docker = input('php_version');
        $list = D('Soft')->getPHPEext($docker);
        $html = '';
        foreach ($list as $k => $vo) {
            $html .= '<input type="checkbox" name="php_ext[' . $k . ']" title="' . $k . '" value="' . $k . '">';
        }
        return $this->success($html);
    }

    function getAllSoft()
    {
        $field = 'title,dev,intro,is_del,git_url';
        $list = find_data(M('install')->where('is_audit', 1)->field($field)->select());
        return json($list);
    }

    function updateSoft()
    {
        //每天只更新一次
//        $lock = date('Ymd');
//        $hasUpdate = S($lock);
//        if ($hasUpdate) return $this->error('今天已经更新过');
//        S($lock, 1, 86400);

        $url = "https://www.bctos.cn/index.php?s=/home/index/getInstallList";
        $content = wp_file_get_contents($url);
        $lists = json_decode($content, true);
        if (empty($lists)) return $this->error('没有需要更新的');

        $has = M('install')->column('id', 'bctos_id');
        $add = [];

        foreach ($lists as $vo) {
            $t = $vo['id'];
            unset($vo['status'], $vo['id']);
            if (isset($has[$t])) {
                //更新
                M('install')->where('bctos_id', $t)->update($vo);
            } else {
                //新增
                $vo['bctos_id'] = $t;
                $add[] = $vo;
            }
        }
        if (!empty($add)) {
            M('install')->insertAll($add);
        }

        return $this->success('更新成功');
    }
}
