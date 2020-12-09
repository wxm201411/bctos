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

        $cronArr = M('cron')->where('type', 1)->column('id', 'site_id');

        $this->assign('page_tips', "使用小韦云面板创建站点时会自动设置PHP容器默认的www-data用户。并自动在<a href='" . U('admin/cron/lists') . "'>[计划任务]</a>中添加定时备份网站任务，您可以自定义备份参数");
        $model = $this->getModel();
        $list_data = $this->getModelList($model);
        foreach ($list_data['list_data'] as &$vo) {
            if (empty($vo['backup_at'])) {
                $vo['backup_at'] = '未备份';
            }
            $vo['backup_at'] = "<a class='backup' rel='{$vo['id']}' title='{$vo['title']}'>{$vo['backup_at']}</a> ";
            //dump($vo);
            $vo['urls'] = "<a class='set-site' rel='{$vo['id']}' title='{$vo['title']}'>设置</a> " . $vo['urls'];
            $vo['title'] = "<a class='set-site' rel='{$vo['id']}' title='{$vo['title']}'>{$vo['title']}</a>";
            $vo['path'] = "<a target='_blank' href='" . U('files?id=' . $vo['id']) . "'>{$vo['path']}</a>";
        }
        $this->assign($list_data);
        return $this->fetch();
    }

    function check_web()
    {
        //静默启动WEB全部服务
        ssh_execute(SITE_PATH . '/scripts/sys/initWebDocker.sh');
        return $this->success('启动成功');
    }

    function parseDomain($domain = '')
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
            if (strpos($data['path'], '/bctos/wwwroot') === false) {
                return $this->error("网站必须放到/bctos/wwwroot目录下，否则无法访问");
            }
            list($data['title'], $server_name, $listen) = $this->parseDomain($data['domain']);
            $data = $this->checkData($data, $model);
            $conf = $data['title'] . '.conf';
            $db_name = str_replace('.', '_', $data['title']);
            $database_id = 0;
            try {
                //判断站点是否存在
                if (is_dir('/bctos/wwwroot/' . $data['title']) || $data['title'] == 'bctos.cn' || $data['title'] == 'stop') {
                    throw new \think\Exception("该网站已存在，请删除后再试", 10);
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
                //增加nginx配置文件
                $path = $data['path']; //换成容器内的路径
                $content = <<<str
server {
{$listen}
    server_name  {$server_name};
    root   {$path};
    index  index.html index.php;

    #伪静态规则
    include /etc/nginx/rewrite/{$data['title']}.rewrite.conf;
    
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
    access_log  /bctos/logs/{$data['title']}.log;
    error_log  /bctos/logs/{$data['title']}.error.log;    
}
str;
                file_put_contents(SITE_PATH . '/runtime/' . $conf, $content);
                //增加数据库
                if ($data['database'] != 'not') {
                    //判断数据库是否存在
                    $data['db_name'] = $db_name;
                    $data['power'] = 'localhost';
                    $res = D('Database')->addDb($data);
                    if ($res['code'] == 1) {
                        throw new \think\Exception('db:' . $res['msg'], 13);
                    } else {
                        $database_id = $res['msg'];
                    }
                }
                $res = ssh_execute(SITE_PATH . '/scripts/sys/createSite.sh ' . SITE_PATH . '/runtime/' . $conf . ' ' . $data['path']);
                if ($res['code'] == 1) {
                    throw new \think\Exception($res['msg'], 13);
                }
            } catch (\Exception $e) {
                @unlink(SITE_PATH . '/runtime/' . $conf);

                $code = $e->getCode();
                if ($code != 10) {
                    //回滚
                    $database_id > 0 && D('Database')->delDb($database_id);
                }
                return $this->error($e->getMessage());
            }

            // dump($data);exit;
            $id = $Model->insertGetId($data);
            if ($id) {
                //自动增加定时任务
                D('Cron')->addCronAuto(1, $id, $data['title'], $data['path']);
                return $this->success('添加成功！', U('lists?model=' . $model['name'], $this->get_param));
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
            $this->assign('db_passwd', uniqid());
            // dump($fields);

            $ssh = ssh_execute("docker ps --format '{{.Names}}' | egrep 'php|mysql'");
            $arr = wp_explode($ssh['msg']);
            $dbs = $phps = [];
            foreach ($arr as $v) {
                if (empty($v)) continue;

                if (strpos($v, 'php') === false) {
                    $dbs[] = $v;
                } else {
                    $phps[] = $v;
                }
            }

            $this->assign('dbs', $dbs);
            $this->assign('phps', $phps);

            return $this->fetch();
        }
    }

    function delCron($id)
    {
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

    function edit()
    {
        $id = input('id/d');
        $data = M('site')->where('id', $id)->find();
        $arr = wp_explode($data['domain']);

        $lists = [];
        foreach ($arr as $k => $vo) {
            $arr2 = explode(':', $vo, 2);
            $p = isset($arr2[1]) ? $arr2[1] : 80;

            $lists[$k] = ['title' => $arr2[0], 'port' => $p];
        }
        $this->assign('lists', $lists);
        $this->assign('info', $data);

        return $this->fetch();
    }

    function domain()
    {
        $model = $this->getModel();
        $id = input('id/d');
        $data = M('site')->where('id', $id)->find();
        if (request()->isPost()) {
            $Model = D($model['name']);
            // 获取模型的字段信息
            $post = input('post.');
            if (empty($post['domain'])) {
                return $this->error('域名不能为空');
            }
            $domain = $this->parseDomain($post['domain']);
            $post['domain'] = $data['domain'] . PHP_EOL . $post['domain'];

            $conf = $data['title'] . '.conf';
            //增加nginx配置文件
            $res = ssh_execute(SITE_PATH . '/scripts/sys/addDomain.sh ' . $conf . ' ' . $domain[1]);
            if ($res['code'] == 1) {
                return $this->error($res['msg']);
            }
            // dump($data);exit;
            $id = $Model->where('id', $id)->update(['domain' => $post['domain']]);
            if ($id) {
                return $this->success('添加' . $model['title'] . '成功！');
            } else {
                return $this->error($Model->getError());
            }
        } else {
            $arr = wp_explode($data['domain']);
            $ssl = json_decode($data['ssl']);

            $lists = [];
            foreach ($arr as $k => $vo) {
                $arr2 = explode(':', $vo, 2);
                $p = isset($arr2[1]) ? $arr2[1] : 80;
                $t = $arr2[0];
                $over_time = '未部署证书';
                if (isset($ssl[$t])) {
                    if ($ssl[$t]['over_time'] > 0) {
                        $over_time = time_format($ssl[$t]['over_time']);
                    } else {
                        $res = ssh_execute("openssl x509 -in /bctos/server/nginx/conf.d/cert/$t/ssl.pem -noout -dates");
                        if ($res['code'] == 1) {
                            $over_time = '-';
                        } else {
                            $ssl = json_decode($data['ssl'], true);
                            $ssl[$t]['over_time'] = $over_time = strtotime($res['msg']);
                            M('site')->where('id', $id)->update(['ssl' => json_encode($ssl)]);

                            $over_time = time_format($over_time);
                        }
                    }
                }

                $lists[$k] = ['title' => $t, 'port' => $p, 'time' => $over_time];
            }
            $this->assign('lists', $lists);
            $this->assign('info', $data);

            return $this->fetch();
        }
    }

    function delDomain()
    {
        $id = input('id/d');
        $key = input('key/d');
        $data = M('site')->where('id', $id)->find();
        $arr = wp_explode($data['domain']);
        if (count($arr) < 2) {
            return $this->error('网站至少要保留一个域名');
        }
        foreach ($arr as $k => $vo) {
            if ($k == $key) {
                $arr2 = explode(':', $vo, 2);
                $domain = $arr2[0];
                unset($arr[$k]);
            }
        }

        $conf = $data['title'] . '.conf';
        //增加nginx配置文件
        $res = ssh_execute(SITE_PATH . '/scripts/sys/delDomain.sh ' . $conf . ' ' . $domain);
        if ($res['code'] == 1) {
            return $this->error($res['msg']);
        }

        M('site')->where('id', $id)->update(['domain' => implode(PHP_EOL, $arr)]);
        return $this->success('删除成功');
    }

    function del()
    {
        $model = $this->getModel();

        $map['id'] = I('id');
        $Model = D($model['name']);
        $data = $Model->where(wp_where($map))->find();
        $conf = $data['title'] . '.conf';
        $db_name = str_replace('.', '_', $data['title']);

        $res = ssh_execute(SITE_PATH . '/scripts/sys/delSite.sh ' . $conf . ' ' . str_replace('/bctos/wwwroot/', '', $data['path']));
        if ($res['code'] == 1) {
            return $this->error($res['msg']);
        }

        @unlink(SITE_PATH . '/runtime/' . $conf);
        if ($data['database'] == 'mysql') {
            D('Database')->delDb($data['database_id']);
        }

        if ($Model->where(wp_where($map))->delete()) {
            //自动删除定时任务
            D('Cron')->delCronAuto($data['id'], 1);

            return $this->success('删除成功');
        } else {
            return $this->error('删除失败！');
        }
    }

    function switchAjaxUpdate()
    {
        $table = input('table');
        $field = input('field');
        $value = input('value');

        $id = input('id');
        $where_field = $table == 'user' ? 'uid' : 'id';
        M($table)->where($where_field, $id)->update([$field => $value]);

        $data = M('site')->where('id', $id)->find();
        if ($value == 1) { //启用
            $serach = 'stop';
            $replace = $data['title'];
        } else { //停用
            $serach = $data['title'];
            $replace = 'stop';
        }
        $conf = $data['title'] . '.conf';
        //dump(SITE_PATH . "/scripts/sys/setSiteStatus.sh {$conf} /bctos/wwwroot/{$serach} /bctos/wwwroot/{$replace}");exit;
        //重启nginx
        ssh_execute(SITE_PATH . "/scripts/sys/setSiteStatus.sh {$conf} {$serach} {$replace}");


        return $this->success('保存成功');
    }

    function dir()
    {
        $map['id'] = input('id/d');
        $data = M('site')->where(wp_where($map))->find();
        if (IS_POST) {
            $post = input('post.');
            isset($post['open_basedir']) || $post['open_basedir'] = 0;
            isset($post['recode_log']) || $post['recode_log'] = 0;
            if (strpos($post['path'], '/bctos/wwwroot') === false) {
                return $this->error("网站必须放到/bctos/wwwroot目录下，否则无法访问");
            }

            $basedir = $post['open_basedir'];
            $log = $post['recode_log'];
            $new_path = rtrim(rtrim($post['path'], '/') . '/' . trim($post['public_path'], '/'));
            $old_path = rtrim(rtrim($data['path'], '/') . '/' . trim($data['public_path'], '/'));
            if ($new_path != $old_path) {
                $root = trim(str_replace('/bctos/wwwroot/', '', $new_path), '/');
            } else {
                $root = '-';
            }

            $conf = $data['title'] . '.conf';
            $res = ssh_execute(SITE_PATH . '/scripts/sys/setSiteDir.sh ' . $conf . ' ' . $root . ' ' . $basedir . ' ' . $log);
            if ($res['code'] == 1) {
                return $this->error($res['msg']);
            }


            M('site')->where(wp_where($map))->update($post);
            return $this->success('保存成功');
        } else {
            $this->assign('data', $data);
            $res = ssh_execute("ls -F {$data['path']} | grep '/$'");
            $dirs = wp_explode(str_replace('/', '', $res['msg']));
            $this->assign('dirs', $dirs);

            return $this->fetch();
        }
    }

    function rewrite()
    {
        $map['id'] = input('id/d');
        $data = M('site')->where(wp_where($map))->find();
        $conf = $data['title'] . '.conf';
        if (IS_POST) {
            $post = input('post.');
            $rewrite = $post['rewrite'];
            unset($post['rewrite']);
            $file = SITE_PATH . '/runtime/' . $conf;
            file_put_contents($file, $rewrite);

            $res = ssh_execute("\cp -f {$file} /bctos/server/nginx/rewrite/{$conf};docker exec -i nginx nginx -s reload");
            if ($res['code'] == 1) {
                return $this->error($res['msg']);
            }
            @unlink($file);
            M('site')->where(wp_where($map))->update($post);
            return $this->success('保存成功');
        } else {
            $res = ssh_execute("touch /bctos/server/nginx/rewrite/{$conf} | cat /bctos/server/nginx/rewrite/{$conf}");
            $data['rewrite'] = $res['msg'];
            $this->assign('data', $data);

            $mods = ['wordpress', 'thinkphp', 'laravel5', 'EduSoho', 'EmpireCMS', 'dabr', 'dbshop', 'dedecms', 'default', 'discuz', 'discuzx', 'discuzx2', 'discuzx3', 'drupal', 'ecshop', 'emlog', 'maccms', 'mvc', 'niushop', 'phpcms', 'phpwind', 'sablog', 'seacms', 'shopex', 'typecho', 'typecho2', 'wp2', 'zblog',];
            $this->assign('mods', $mods);

            return $this->fetch();
        }
    }

    function index()
    {
        $map['id'] = input('id/d');
        $data = M('site')->where(wp_where($map))->find();
        $conf = $data['title'] . '.conf';
        if (IS_POST) {
            $index = input('post.index');
            if (empty($index)) {
                return $this->error('至少需要一个入口文件');
            }
            $index = implode(' ', wp_explode($index));

            $res = ssh_execute("sed -i -r 's/ index[ \\t].*;/ index {$index};/' /bctos/server/nginx/conf.d/{$conf};docker exec -i nginx nginx -s reload");
            if ($res['code'] == 1) {
                return $this->error($res['msg']);
            }
            return $this->success('保存成功');
        } else {
            $res = ssh_execute("egrep '^[ \\t]*index ' /bctos/server/nginx/conf.d/{$conf} | sed 's/index//'|sed 's/;//'");
            $data['index'] = implode(PHP_EOL, wp_explode($res['msg']));
            $this->assign('data', $data);

            return $this->fetch();
        }
    }

    function conf()
    {
        $map['id'] = input('id/d');
        $data = M('site')->where(wp_where($map))->find();
        $conf = $data['title'] . '.conf';
        if (IS_POST) {
            $index = input('post.index');
            if (empty($index)) {
                return $this->error('配置文件不能为空');
            }
            $file = SITE_PATH . '/runtime/' . $conf;
            file_put_contents($file, $index);

            $res = ssh_execute("\cp -f {$file} /bctos/server/nginx/conf.d/{$conf};docker exec -i nginx nginx -s reload");
            if ($res['code'] == 1) {
                return $this->error($res['msg']);
            }
            @unlink($file);
            return $this->success('保存成功');
        } else {
            $res = ssh_execute("cat /bctos/server/nginx/conf.d/{$conf}");
            $data['index'] = $res['msg'];
            $this->assign('data', $data);

            return $this->fetch();
        }
    }

    function addCert()
    {
        $map['id'] = input('id/d');
        $data = M('site')->where(wp_where($map))->find();
        $conf = $data['title'] . '.conf';
        if (IS_POST) {
            $post = input('post.');
            if (empty($post['key'])) {
                return $this->error('请上传密钥文件');
            }
            if (empty($post['pem'])) {
                return $this->error('请上传证书文件');
            }
            $ssl = json_decode($data['ssl'], true);
            $d = $post['domain'];
            unset($post['domain']);
            $ssl[$d] = $post;

            $pem = str_replace(SITE_URL, SITE_PATH . '/public', get_file_url($post['pem']));
            if (!empty($post['crt'])) {
                //root_crt与pem文件合并
                $content = file_get_contents($pem);
                $crt = str_replace(SITE_URL, SITE_PATH . '/public', get_file_url($post['crt']));
                $content .= PHP_EOL . file_get_contents($crt);
                $pem = SITE_PATH . '/runtime/tmp.pem';
                file_put_contents($pem, $content);
            } else {
                $pem = str_replace(SITE_URL, SITE_PATH . '/public', get_file_url($post['pem']));
            }
            $key = str_replace(SITE_URL, SITE_PATH . '/public', get_file_url($post['key']));

            $res = ssh_execute(SITE_PATH . "/scripts/sys/setSiteDir.sh $d $key $pem");
            if ($res['code'] == 1) {
                return $this->error($res['msg']);
            } else {
                $ssl[$d]['over_time'] = strtotime($res['msg']);
            }

            M('site')->where('id', $map['id'])->update(['ssl' => json_encode($ssl), 'ssl_open' => 1]);
            return $this->success('保存成功');
        } else {
            $this->assign('data', $data);

            return $this->fetch();
        }
    }

    function closeCert()
    {
        $map['id'] = input('id/d');
        $data = M('site')->where(wp_where($map))->find();

        $checked = input('checked/d', 0);

        $res = ssh_execute(SITE_PATH . "/scripts/sys/closeSiteSSL.sh {$data['title']} $checked");
        if ($res['code'] == 1) {
            return $this->error($res['msg']);
        }

        M('site')->where('id', $map['id'])->update(['ssl_open' => $checked]);
        return $this->success('保存成功');
    }

    function forceCert()
    {
        $map['id'] = input('id/d');
        $data = M('site')->where(wp_where($map))->find();

        $checked = input('checked');

        $res = ssh_execute(SITE_PATH . "/scripts/sys/forceSiteSSL.sh {$data['title']} $checked");
        if ($res['code'] == 1) {
            return $this->error($res['msg']);
        }

        M('site')->where('id', $map['id'])->update(['ssl_force' => $checked]);
        return $this->success('保存成功');
    }

    function phpVersion()
    {
        $map['id'] = input('id/d');
        $data = M('site')->where(wp_where($map))->find();
        if (IS_POST) {
            $php_version = input('post.php_version');
            if ($php_version == $data['php_version'] && false) {
                return $this->success('保存成功');
            }

            $res = ssh_execute(SITE_PATH . "/scripts/sys/phpVersion.sh {$data['title']} $php_version");
            if ($res['code'] == 1) {
                return $this->error($res['msg']);
            }

            M('site')->where('id', $map['id'])->update(['php_version' => $php_version]);
            return $this->success('保存成功');
        } else {
            $res = ssh_execute("docker ps --format \"{{.Names}}\" | grep \"php\"");
            $data['versions'] = wp_explode($res['msg']);
            $this->assign('data', $data);

            return $this->fetch();
        }
    }

    function files()
    {
        $id = input('id/d', 0);
        if ($id > 0) {
            $path = M('site')->where('id', $id)->value('path');
            $path = '/myos/' . trim(urldecode($path), '/');
        } else {
            $path = '/myos/bctos/wwwroot';
        }

        $token = base64_encode('admin') . '|' . md5('admin' . config('database.connections.mysql.password'));

        return redirect(SITE_URL . '/kod/index.php?user/loginSubmit&login_token=' . urlencode($token) . '&link=' . urlencode(SITE_URL . '/kod/?explorer&path=' . $path));

//        $res = wp_file_get_contents(SITE_URL . '/kodbox/index.php?user/index/loginSubmit&loginToken=' . urlencode($token));
//        $res = json_decode($res, true);
//
//        if ($res['code'] == false) {
//            return $this->error($res['data']);
//        } else {
//            return redirect(SITE_URL . '/kodbox/#explorer&path=' . $path . '&accessToken=' . urlencode($res['info']));
//        }
    }

    function server()
    {
        $map['id'] = input('id/d');
        $data = M('site')->where(wp_where($map))->find();
        $conf = "/bctos/server/nginx/conf.d/{$data['title']}.conf";
        if (IS_POST) {
            $type = input('type', 'start');
            if ($type == 'stop') {
                $status = 0;
                $res = ssh_execute("sed -i -r 's:{$data['path']}:/bctos/wwwroot/stop:' $conf;docker restart nginx;");
            } else {
                $status = 1;
                $res = ssh_execute("sed -i -r 's:/bctos/wwwroot/stop:{$data['path']}:' $conf;docker restart nginx;");
            }
            M('site')->where(wp_where($map))->update(['status' => $status]);
            return $this->success($res['msg']);
        } else {
            $res = ssh_execute("cat $conf |grep '/bctos/wwwroot/stop'");
            $this->assign('status', empty($res['msg']) ? 1 : 0);
            $this->assign('data', $data);
            return $this->fetch();
        }
    }
}
