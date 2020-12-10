<?php
// +----------------------------------------------------------------------
// | 一机一码 [ 公众号和小程序运营管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.bctos.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 凡星 <xw@bctos.cn> <QQ:203163051>
// +----------------------------------------------------------------------
namespace app\admin\controller;


class Soft extends Admin
{
    function add()
    {
        $model = $this->getModel();
        if (request()->isPost()) {
            $Model = D($model['name']);
            // 获取模型的字段信息
            $data = input('post.');
            $data = $this->checkData($data, $model);
            if ($Model->where('docker', $data['docker'])->value('id')) {
                return $this->error('该容器名已经存在，请换名再试');
            }
            // dump($data);exit;
            $id = $Model->insertGetId($data);
            if ($id) {
                $d = $data['docker'];
                $yml = <<<EOF
version: "3.1"
networks:
  default:
    external:
      name: bctos

services:
  {$d}:
    hostname: {$d}
    container_name: {$d}
    image: {$data['image']}
    restart: unless-stopped
EOF;
                $file = SITE_PATH . '/runtime/docker-compose.yml';
                file_put_contents($file, $yml);
                ssh_execute("cd /bctos/server;mkdir {$d};mv $file {$d}/docker-compose.yml;");

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
            $this->assign('iframeClass', 'iframe');

            return $this->fetch();

        }
    }

    function lists()
    {
        $this->assign('page_tips', "建议按需开启mysql数据库，同时开启多个版本的数据库可能导致服务器内存不足；安装服务时可能要下载镜像，需耐心等待");
        $res = ssh_execute("docker images --format '{{.Repository}}|||{{.Tag}}';docker ps -a --format '{{.Image}}|||{{.Status}}|||{{.Names}}'");
        if ($res['code'] == 1) {
            return $this->error($res['msg']);
        }
        $rows = wp_explode($res['msg'], "\n");
        $images = $contains = [];
        foreach ($rows as $r) {
            if (empty($r)) continue;

            $arr = explode('|||', $r);
            if (count($arr) == 2) {
                $images[$arr[0] . ':' . $arr[1]] = 1;
            } else {
                $contains[$arr[2]] = ['image' => $arr[0], 'status' => substr($arr[1], 0, 2) == 'Up' ? 2 : 3];
            }
        }

        $model = $this->getModel();
        $dataTable = D('common/Models')->getFileInfo($model);
        //以文件配置为最高优先级
        if (is_array($model) && is_array($dataTable->config)) {
            $model = array_merge($model, $dataTable->config);
        }

        $this->assign('add_button', $dataTable->config['add_button']);
        $this->assign('del_button', $dataTable->config['del_button']);
        $this->assign('search_button', $dataTable->config['search_button']);
        $this->assign('check_all', $dataTable->config['check_all']);

        // 解析列表规则
        $list_data = $this->listGrid($model, $dataTable);
        $fields = $list_data['fields'];

        // 搜索条件
        $map = $this->searchMap($model, $list_data['db_fields']);
        $row = empty($model['list_row']) ? 20 : $model['list_row'];

        // 读取模型数据列表
        $order = 'id desc';
        if (empty($order) && isset($_REQUEST['order'])) {
            $order = input('order', 'id') . ' ' . input('by', 'desc');
        }

        // dump ( $order );
        $name = $dataTable->config['name'];
        $db_field = true;

        $wp_where = wp_where($map);
        $data = M($name)->field($db_field)->where($wp_where)->order($order)->paginate($row);

        $data = dealPage($data);
        if (!empty($list_data)) {
            $list_data = array_merge($list_data, $data);
        } else {
            $list_data = $data;
        }
        foreach ($list_data['list_data'] as &$vo) {
            if (isset($contains[$vo['docker']])) {
                $vo['status'] = $contains[$vo['docker']]['status'];
            } elseif (isset($images[$vo['image']])) {
                $vo['status'] = 1;
            } else {
                $vo['status'] = 0;
            }

        }

        if (!empty($model)) {
            $list_data['list_data'] = $this->parseListData($list_data['list_data'], $model);
        }

        $this->assign($list_data);


        return $this->fetch();
    }

    function getSoftInfo()
    {
        $id = id();
        $info = M('soft')->where('id', $id)->find();
        $this->assign('data', $info);
        return $info;
    }

    function config_set()
    {
        $info = $this->getSoftInfo();
        return $this->fetch($info['config_html']);
    }

    function dockerfile()
    {
        $info = $this->getSoftInfo();
        $name = $info['docker'];
        $conf = '/bctos/server/' . $name . '/Dockerfile';
        //获取旧镜像名称
        $res = ssh_execute('cat /bctos/server/' . $name . '/docker-compose.yml');
        $yml = yaml_parse($res['msg']);
        if (IS_POST) {
            $index = input('post.index');
            if (empty($index)) {
                return $this->error('配置文件不能为空');
            }
            $file = SITE_PATH . '/runtime/Dockerfile';
            file_put_contents($file, $index);


            $image = $yml['services'][$name]['image'];

            //删除旧镜像，生成新镜像，容器重启
            $res = ssh_execute("\cp -f {$file} {$conf};docker rmi $image;cd /bctos/server/$name;docker-compose down;docker-compose up -d;");
            if ($res['code'] == 1) {
                return $this->error($res['msg']);
            }
            @unlink($file);
            return $this->success('保存成功');
        } else {
            if (isset($yml['services'][$name]['build'])) {
                $res = ssh_execute("[[ -f $conf ]] && cat {$conf} || echo ''");
                $info['index'] = $res['msg'];
                $this->assign('build', 1);
            } else {
                $this->assign('build', 0);
            }

            $this->assign('data', $info);

            return $this->fetch();
        }
    }

    function compose()
    {
        $info = $this->getSoftInfo();
        $name = $info['docker'];
        $conf = '/bctos/server/' . $name . '/docker-compose.yml';
        if (IS_POST) {
            $index = input('post.index');
            if (empty($index)) {
                return $this->error('配置文件不能为空');
            }
            $file = SITE_PATH . '/runtime/compose.yml';
            file_put_contents($file, $index);

            //docker-compose 重启，注必须要先down再up,不能使用restart,因为它不会加载新的docker-compose相关文件的新的改动
            $res = ssh_execute("\cp -f {$file} {$conf};cd /bctos/server/$name;docker-compose down;docker-compose up -d");
            if ($res['code'] == 1) {
                return $this->error($res['msg']);
            }
            @unlink($file);
            return $this->success('保存成功');
        } else {
            $res = ssh_execute("[[ -f $conf ]] && cat {$conf} || echo ''");
            $info['index'] = $res['msg'];
            $this->assign('data', $info);

            return $this->fetch();
        }
    }

    function nginx_server()
    {
        if (IS_POST) {

            //重启nginx
            $res = ssh_execute("docker exec -i nginx nginx -s reload");
            return $this->success($res['msg']);
        } else {
            return $this->fetch();
        }
    }

    function nginx_server_check()
    {
        echo 'ok';
        exit;
    }

    function nginx_version()
    {
        $info = $this->getSoftInfo();
        if (IS_POST) {
            $post = input('post.');
            if ($info['image'] == $post['image']) {
                return $this->success('保存成功');
            }
            //替换数据表，替换compose.yml,重启compose(先down后up -d)
            M('soft')->where('id', $post['id'])->update(['image' => $post['image']]);

            ssh_execute("cd /bctos/server/nginx;sed -i 's/{$info['image']}/{$post['image']}/g' docker-compose.yml;docker-compose down;docker-compose up -d");
            return $this->success('保存及重启成功');
        } else {
            $images = ['nginx:1.19-alpine', 'nginx:1.18-alpine', 'nginx:1.17-alpine', 'nginx:1.16-alpine', 'nginx:1.15-alpine', 'nginx:1.14-alpine', 'nginx:1.13-alpine', 'nginx:1.12-alpine'];
            $this->assign('images', $images);
            return $this->fetch();
        }
    }

    function nginx_ssl()
    {
        $info = $this->getSoftInfo();
        $res = ssh_execute("grep '443:443' /bctos/server/nginx/docker-compose.yml");
        $info['switch'] = empty($res['msg']) ? 0 : 1;
        if (IS_POST) {
            $image = str_replace('bctos-', '', $info['image']);
            $command = "cd /bctos/server/nginx;";
            if ($info['switch'] == 0) {
                //开启：替换数据表的镜像名，创建Dockerfile,compose.yml增加443,增加build参数，替换compose中是的镜像，重启compose(先down后up -d)
                $command .= "echo 'FROM {$image}
EXPOSE 443' > Dockerfile;" . 'sed -i "/80:80/a\\\\$(grep \'80:80\' docker-compose.yml | sed \'s/80/443/g\')" docker-compose.yml;';
                $s = 'hostname: nginx';
                $command .= "sed -i \"/$s/i\\\\$(grep '$s' docker-compose.yml | sed 's/$s/build: \/bctos\/server\/nginx/g')\" docker-compose.yml;";
                $image = 'bctos-' . $image;
            } else {
                //关闭：替换数据表的镜像名，删除Dockerfile,compose.yml删除443,删除build参数，替换compose中是的镜像，重启compose(先down后up -d)
                $command .= "rm -f Dockerfile;sed -i \"/443:443/d\" docker-compose.yml;sed -i \"/build:/d\" docker-compose.yml;";
            }
            //dump($command);exit;
            $command .= "sed -i 's/{$info['image']}/$image/g' docker-compose.yml;docker-compose down;docker-compose up -d";
            M('soft')->where('id', $info['id'])->update(['image' => $image]);
            ssh_execute($command);
            return $this->success('保存及重启成功');
        } else {
            $this->assign('status', $info['switch']);
            return $this->fetch();
        }
    }

    function nginx_conf()
    {
        $info = $this->getSoftInfo();
        $conf = "/bctos/server/{$info['docker']}/nginx.conf";
        if (IS_POST) {
            $index = input('post.index');
            if (empty($index)) {
                return $this->error('配置文件不能为空');
            }
            $file = SITE_PATH . '/runtime/nginx.conf';
            file_put_contents($file, $index);

            $res = ssh_execute("\cp -f {$file} {$conf};docker exec -i {$info['docker']} nginx -s reload");
            if ($res['code'] == 1) {
                return $this->error($res['msg']);
            }
            @unlink($file);
            return $this->success('保存成功');
        } else {
            $res = ssh_execute("cat {$conf}");
            $info['index'] = $res['msg'];
            $this->assign('data', $info);

            return $this->fetch();
        }
    }

    function nginx_edit()
    {
        $info = $this->getSoftInfo();
        $conf = "/bctos/server/{$info['docker']}/nginx.conf";
        $res = ssh_execute("cat {$conf}");
        $arr = wp_explode($res['msg'], "\r\n");

        foreach ($arr as $a) {
            $a = trim(trim($a), ';');
            //dump($a);
            if (substr($a, 0, 1) == '#' || strpos($a, ' ') === false) {
                continue;
            }
            list($key, $vo) = wp_explode($a);
            if (in_array($key, ['client_max_body_size', 'client_header_buffer_size', 'client_body_buffer_size', 'gzip_min_length'])) {
                $vo = intval($vo);
            }

            $info[$key] = $vo;
        }
        if (IS_POST) {
            $post = input('post.');
            $command = "";
            foreach ($post as $k => $p) {
                if (empty($p) && $p !== '0') {
                    return $this->error($k . '的值不能为空');
                }
                if ($p != $info[$k]) {
                    $command .= "sed -i -r 's/{$k}[ \t]+{$info[$k]}/{$k} {$p}/' {$conf};";
                }
            }
            //dump($command);
            //exit;
            $res = ssh_execute("{$command} docker exec -i {$info['docker']} nginx -s reload");
            if ($res['code'] == 1) {
                return $this->error($res['msg']);
            }
            return $this->success('保存成功');
        } else {
            $this->assign('data', $info);
            return $this->fetch();
        }
    }

    function docker_log()
    {
        $info = $this->getSoftInfo();
        $res = ssh_execute("docker logs {$info['docker']}");
        empty($res['msg']) && $res['msg'] = '暂无日志';
        $this->assign('logs', $res['msg']);

        return $this->fetch();

    }


    function mysql_server()
    {
        $info = $this->getSoftInfo();
        if (IS_POST) {
            //重启nginx
            $res = ssh_execute("docker restart {$info['docker']}");
            return $this->success($res['msg']);
        } else {
            return $this->fetch();
        }
    }

    function mysql_server_check()
    {
        //TODO
        $info = M('user')->find();
        if (isset($info['nickname'])) {
            echo 'ok';
        } else {
            echo 'no';
        }
        exit;
    }

    function mysql_conf()
    {
        $info = $this->getSoftInfo();
        $conf = "/bctos/server/{$info['docker']}/my.cnf";
        if (IS_POST) {
            $index = input('post.index');
            if (empty($index)) {
                return $this->error('配置文件不能为空');
            }
            $file = SITE_PATH . '/runtime/my.cnf';
            file_put_contents($file, $index);

            $res = ssh_execute("\cp -f {$file} {$conf};docker restart {$info['docker']}");
            if ($res['code'] == 1) {
                return $this->error($res['msg']);
            }
            @unlink($file);
            return $this->success('保存成功');
        } else {
            $res = ssh_execute("cat {$conf}");
            $info['index'] = $res['msg'];
            $this->assign('data', $info);

            return $this->fetch();
        }
    }

    function mysql_root()
    {
        $info = $this->getSoftInfo();
        $conf = "/bctos/server/{$info['docker']}/docker-compose.yml";
        if (IS_POST) {
            $old = input('old_pwd');
            $pwd = input('pwd');
            if ($old == $pwd) {
                return $this->success('修改成功');
            }
            $res = ssh_execute(SITE_PATH . "/scripts/sys/editUserPwd.sh {$pwd} root {$info['docker']}");
            if ($res['code'] == 1) {
                return $this->error($res['msg']);
            }
            return $this->success('修改密码成功');
        } else {
            $res = ssh_execute("cat {$conf}| grep MYSQL_ROOT_PASSWORD|sed 's/MYSQL_ROOT_PASSWORD://'|sed 's/ //g'");
            $info['pwd'] = trim($res['msg']);
            $this->assign('data', $info);

            return $this->fetch();
        }
    }

    function mysql_edit()
    {

    }

    function mysql_slow()
    {
        $info = $this->getSoftInfo();
        $res = ssh_execute("file=$(grep slow_query_log_file /bctos/server/{$info['docker']}/my.cnf | sed -r \"s/slow_query_log_file[ ]*=[ ]*//\");[ -f \$file ] && cat \$file || echo \"暂无日志\"");
        $this->assign('logs', $res['msg']);

        return $this->fetch('docker_log');
    }


    function server()
    {
        $info = $this->getSoftInfo();
        if (IS_POST) {
            $type = input('type', 'restart');
            if ($type == 'stop') {
                $res = ssh_execute("docker stop " . $info['docker']);
            } elseif ($type == 'start') {
                $res = ssh_execute("[ -z $(docker ps -a --format '{{.Names}}'|grep '{$info['docker']}'|sed 's/ //g') ] && cd /bctos/server/{$info['docker']} && docker-compose up -d || docker start {$info['docker']}");
            } else {
                //重启
                $res = ssh_execute("docker restart " . $info['docker']);
            }
            return $this->success($res['msg']);
        } else {
            $res = ssh_execute("docker ps --format '{{.Names}}' |grep '{$info['docker']}'");
            $this->assign('status', empty($res['msg']) ? 0 : 1);
            return $this->fetch();
        }
    }

    function php_conf()
    {
        $info = $this->getSoftInfo();
        $type = input('type', 'php');
        if ($type == 'fpm') {
            $conf = '/bctos/server/' . $info['docker'] . '/php-fpm.conf';
        } else {
            $conf = '/bctos/server/' . $info['docker'] . '/php.ini';
        }

        if (IS_POST) {
            $index = input('post.index');
            if (empty($index)) {
                return $this->error('配置文件不能为空');
            }
            $file = SITE_PATH . '/runtime/php.ini';
            file_put_contents($file, $index);

            $res = ssh_execute("\cp -f {$file} {$conf};docker restart " . $info['docker']);
            if ($res['code'] == 1) {
                return $this->error($res['msg']);
            }
            @unlink($file);
            return $this->success('保存成功');
        } else {
            $res = ssh_execute("cat {$conf}");
            $info['index'] = $res['msg'];
            $this->assign('data', $info);

            return $this->fetch();
        }
    }

    function php_edit()
    {

    }

    function php_slow()
    {
        $info = $this->getSoftInfo();
        $res = ssh_execute("cat /bctos/logs/{$info['docker']}.log.slow");
        //dump($res);exit;
        empty($res['msg']) && $res['msg'] = '暂无日志';
        $this->assign('logs', $res['msg']);

        return $this->fetch('docker_log');
    }

    function phpinfo()
    {
        $info = $this->getSoftInfo();
        $res = ssh_execute("docker exec {$info['docker']} sh -c 'php -i'");
        //phpinfo();
        echo $res['msg'];
    }

    function php_ext()
    {
        $info = $this->getSoftInfo();
        $data = $this->getExts($info['docker']);
        if (IS_POST) {
            $post = input('post.');
            if (empty($post['ext'])) {
                return $this->error('扩展名称不能为空');
            }
            $ext = $post['ext'];
            $command = "";

            if ($post['type'] == 'install') {
                if (isset($data[$ext])) {
                    return $this->error('该扩展已安装过');
                }
                //安装：先在docker中安装扩展，更新php-dockerfile文件
                $command .= "docker exec {$info['docker']} install-php-extensions {$ext};sed -i 's/ install-php-extensions/ install-php-extensions {$ext} /' /bctos/server/{$info['docker']}/Dockerfile;";
            } else {
                if (!isset($data[$ext])) {
                    return $this->error('该扩展已不存在');
                }
                //卸载：屏蔽配置文件，重启容器，更新php-dockerfile文件
                $command .= "docker exec {$info['docker']} rm -f /usr/local/etc/php/conf.d/docker-php-ext-{$ext}.ini;docker restart php72;sed -i 's/{$ext}//' /bctos/server/{$info['docker']}/Dockerfile;";
            }
            //把当前容器导出为新的镜像
            $command .= "docker commit {$info['docker']} {$info['image']};docker rmi $(docker images -f 'dangling=true' -q);";

            $res = ssh_execute("{$command} docker restart {$info['docker']};");
            if ($res['code'] == 1) {
                return $this->error($res['msg']);
            }
            return $this->success('保存成功');
        } else {
            $exts = D('Soft')->getPHPEext($info['docker']);
            //dump($data);
            foreach ($exts as $k => $e) {
                if (isset($data[$k])) {
                    $url = '<a class="red" rel="' . $k . '">卸载</a>';
                } else {
                    $url = '<a rel="' . $k . '">安装</a>';
                }
                $lists[] = ['name' => $k, 'intro' => $e, 'url' => $url];
            }
            $this->assign('lists', $lists);
            return $this->fetch();
        }
    }

    function getExts($docker)
    {
        $res = ssh_execute("docker exec {$docker} php -m");
        $arr = wp_explode($res['msg'], "\r\n");
        $data = [];
        foreach ($arr as $a) {
            $a = trim(trim($a), ';');
            $data[$a] = $a;
        }
        return $data;
    }

    function php_ext_check()
    {
        $info = $this->getSoftInfo();
        $data = $this->getExts($info['docker']);
        $ext = input('ext');
        $type = input('type');
        if (($type == 'install' && isset($data[$ext])) || ($type == 'uninstall' && !isset($data[$ext]))) {
            echo 'ok';
            exit;
        } else {
            echo 'no';
            exit;
        }
    }

    function install()
    {
        $info = $this->getSoftInfo();
        //判断是否已经下载安装包，否则先下载并解压到server目录；启动容器
        ssh_execute(SITE_PATH . '/scripts/sys/installServer.sh ' . $info['docker']);
        return $this->success('安装成功');
    }

    function uninstall()
    {
        $info = $this->getSoftInfo();
        $d = $info['docker'];
        ssh_execute("cd /bctos/server/$d;docker stop $d;docker rm $d;docker rmi {$info['image']};");
        return $this->success('卸载成功');
    }
    function updateSoft()
    {
        //每天只更新一次
//        $lock = date('Ymd');
//        $hasUpdate = S($lock);
//        if ($hasUpdate) return $this->error('今天已经更新过');
//        S($lock, 1, 86400);

        $url = "https://www.bctos.cn/index.php?s=/home/index/getServerList";
        $content = wp_file_get_contents($url);
        $lists = json_decode($content, true);
        if (empty($lists)) return $this->error('没有需要更新的');

        $dao = M('soft');

        $has = $dao->column('id', 'docker');
        $add = [];

        foreach ($lists as $vo) {
            unset($vo['id']);
            $t = $vo['docker'];
            if (isset($has[$t])) {
                //更新
                $dao->where('id', $has[$t])->update($vo);
            } else {
                //新增
                $add[] = $vo;
            }
        }
        if (!empty($add)) {
            $dao->insertAll($add);
        }
        return $this->success('更新成功');
    }
}
