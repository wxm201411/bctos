<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星 <xw@bctos.cn> <QQ:203163051>
// +----------------------------------------------------------------------
namespace app\admin\controller;


class Site extends Admin
{
    function lists()
    {
        $this->assign('page_tips', "使用小韦面板创建站点时会自动设置PHP容器默认的www-data用户。建站后建议在<a href='" . U('admin/crontab/lists') . "'>[计划任务]</a>中添加定时备份任务");
        return parent::lists();
    }

    function domain($domain = '')
    {
        $arr = wp_explode($domain);

        $server_name = $listen = $title = '';
        $has = [];
        foreach ($arr as $k => $vo) {
            $arr2 = explode(':', $vo, 2);
            $p = isset($arr2[1]) ? $arr2[1] : 80;

            empty($title) && $title = $arr2[0];
            $server_name .= $arr2[0] . ' ';
            if (!isset($has[$p])) {
                $listen .= '    listen       ' . $p . ';' . PHP_EOL;
                $has[$p] = 1;
            }
        }
        return [$title, trim($server_name), $listen];
    }

    function add()
    {
        $model = $this->getModel();
        if (request()->isPost()) {
            $Model = D($model['name']);
            // 获取模型的字段信息
            $data = input('post.');
            list($data['title'], $server_name, $listen) = $this->domain($data['domain']);
            $data = $this->checkData($data, $model);
            // dump($data);exit;
            $id = $Model->insertGetId($data);
            if ($id) {
                //增加nginx配置文件
                $conf = $data['title'] . '.conf';
                $content = <<<str
server {
{$listen}
    server_name  {$server_name};
    root   {$data['path']};
    index  index.html index.php;

    location / {
	   if (!-e \$request_filename){
		  rewrite  ^(.*)$  /index.php?s=$1  last;   break;
	   }
    }
    location ~ \.php$ {
        fastcgi_pass   {$data['php_version']}:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME  \$document_root\$fastcgi_script_name;
        include        fastcgi_params;
    }
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
}
str;
                file_put_contents(SITE_PATH . '/runtime/' . $conf, $content);
                //增加数据库
                if ($data['database'] == 'mysql') {
                    $db_name = str_replace('.', '_', $data['title']);
                    $sql = "CREATE DATABASE IF NOT EXISTS {$db_name} DEFAULT CHARACTER SET {$data['db_set']}";
                    M()->execute($sql);
                    $sql = "CREATE USER '{$data['db_user']}'@'localhost' IDENTIFIED BY '{$data['db_passwd']}'";
                    M()->execute($sql);
                    $sql = "GRANT ALL ON {$db_name}.* TO '{$data['db_user']}'@'localhost'";
                    M()->execute($sql);
                }
                ssh2(SITE_PATH . '/scripts/sys/createSite.sh ' . SITE_PATH . '/runtime/' . $conf . ' ' . $data['path']);
                return $this->success('添加' . $model['title'] . '成功！', U('lists?model=' . $model['name'], $this->get_param));
            } else {
                return $this->error($Model->getError());
            }
        } else {
            $fields = get_model_attribute($model);
            $data = default_form_value($fields);
            $post_url = U('add?model=' . $model['id'], $GLOBALS['get_param']);


            $this->assign('fields', $fields);
            $this->assign('data', $data);
            $this->assign('post_url', $post_url);
            // dump($fields);

            return $this->fetch();

        }
    }
}
