<?php

namespace app\docker\controller;

use app\common\controller\WebBase;

//PC运营管理端的控制器
class Images extends WebBase
{
    public function initialize()
    {
        parent::initialize();

        $res['title'] = '容器管理';
        $res['url'] = U('docker/lists');
        $res['class'] = '';
        $nav[] = $res;

        $res['title'] = '镜像管理';
        $res['url'] = U('images/lists');
        $res['class'] = 'current';
        $nav[] = $res;

        $this->assign('nav', $nav);
    }

    function lists()
    {
        $model = $this->getModel();

        $dataTable = D('common/Models')->getFileInfo($model);

        $this->assign('add_button', $dataTable->config['add_button']);
        $this->assign('del_button', $dataTable->config['del_button']);
        $this->assign('search_button', $dataTable->config['search_button']);
        $this->assign('check_all', $dataTable->config['check_all']);

        $this->assign('top_more_button', [
            ['title' => '下载镜像', 'url' => U('download'), 'class' => 'download'],
        ]);

        // 解析列表规则
        $list_data = $this->listGrid($model, $dataTable);
        $fields = $list_data['fields'];

        $data = M($model['name'])->paginate(100);
        $list = $this->parsePageData($data, $dataTable, $list_data);

        $content = ssh2("docker images --format \"{{.Repository}}|||{{.Tag}}|||{{.ID}}|||{{.CreatedSince}}|||{{.Size}}\"");
        $rows = wp_explode($content, "\n");
        $data = [];
        $size = 0;
        foreach ($rows as $r) {
            if (empty($r)) continue;

            $arr = explode('|||', $r);
            $vo['id'] = $vo['IMAGE_ID'] = $arr[2];
            $vo['REPOSITORY'] = $arr[0];
            $vo['TAG'] = $arr[1];
            $vo['CREATED'] = $arr[3];
            $vo['SIZE'] = $arr[4];

            $vo['urls'] = ' <a class="tr-del" data-url="' . urldecode(U('del?id=' . $vo['id'] . '&name=' . $vo['REPOSITORY'] . '&tag=' . $vo['TAG'])) . '">删除</a>';

            $falt = substr($vo['SIZE'], -2);
            $num = rtrim($vo['SIZE'], $falt);
            if ($falt == 'MB') {
                $size += number_format($num / 1024, 2);
            } else {
                $size += $num;
            }

            $data[] = $vo;
        }
        $list['list_data'] = $data;
        $this->assign($list);

        $this->assign('page_tips', "所有镜像总大小：{$size}GB");

        return $this->fetch();
    }

    function del()
    {
        $id = input('id', '-1');
        $name = input('name', '');
        $tag = input('tag', 'latest');
        $id = input('id', '-1');

        if ($id == -1) {
            ssh2("docker stop $(docker ps -aq);docker rm $(docker ps -aq);docker rmi $(docker images --format {{.ID}})");
        } else {
            if (empty($name) || empty($tag)) {
                ssh2("docker rmi {$id}");
            } else {
                ssh2("docker rmi {$name}:{$tag}");
            }
        }
        return $this->success('删除镜像成功');
    }

    function download()
    {
        $title = input('title');
        if (empty($title)) {
            return $this->error();
        }
        ssh_execute("docker pull " . $title);
        return $this->success('下载成功');
    }
}
