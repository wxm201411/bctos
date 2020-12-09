<?php

namespace app\docker\controller;

use app\common\controller\WebBase;

//PC运营管理端的控制器
class Docker extends WebBase
{
    var $canNotStop = ['panel', 'php72', 'mysql57', 'nginx'];
    var $canNotStopIds = "docker ps -a | sed '/\(panel\|php72\|mysql57\|nginx\)/d' | awk '{print $1}'|sed '1d'";

    public function initialize()
    {
        parent::initialize();

        $res['title'] = '容器管理';
        $res['url'] = U('docker/lists');
        $res['class'] = 'current';
        $nav[] = $res;

        $res['title'] = '镜像管理';
        $res['url'] = U('images/lists');
        $res['class'] = '';
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
            ['title' => '全部启动', 'url' => U('start?id=-1'), 'class' => 'ajax-get'],
            ['title' => '全部停止', 'url' => U('stop?id=-1'), 'class' => 'ajax-get'],
            ['title' => '全部删除', 'url' => U('del?id=-1'), 'class' => 'ajax-get'],
        ]);

        // 解析列表规则
        $list_data = $this->listGrid($model, $dataTable);
        $fields = $list_data['fields'];

        $data = M($model['name'])->paginate(100);
        $list = $this->parsePageData($data, $dataTable, $list_data);

        $content = ssh2("docker ps -a --format \"{{.ID}}|||{{.Image}}|||{{.Command}}|||{{.RunningFor}}|||{{.Status}}|||{{.Ports}}|||{{.Names}}\"");
        $rows = wp_explode($content, "\n");
        $data = [];
        foreach ($rows as $r) {
            if (empty($r)) continue;

            $arr = explode('|||', $r);
            $vo['id'] = $vo['CONTAINER_ID'] = $arr[0];
            $vo['IMAGE'] = $arr[1];
            $vo['COMMAND'] = $arr[2];
            $vo['CREATED'] = $arr[3];
            $vo['STATUS'] = $arr[4];
            $vo['PORTS'] = $arr[5];
            $vo['NAMES'] = $arr[6];

            if (in_array($vo['NAMES'], $this->canNotStop)) {
                $vo['urls'] = '不可操作';
            } else {
                if (substr($vo['STATUS'], 0, 2) == 'Up') {
                    $vo['urls'] = '<a class="ajax-get" target="_self" href="' . urldecode(U('stop?id=' . $vo['id'])) . '">停止</a>';
                } else {
                    $vo['urls'] = '<a class="ajax-get" target="_self" href="' . urldecode(U('start?id=' . $vo['id'])) . '">启动</a>';
                }
                $vo['urls'] .= ' <a class="tr-del" data-url="' . urldecode(U('del?id=' . $vo['id'])) . '">删除</a>';
            }

            $data[] = $vo;
        }
        $list['list_data'] = $data;
        $this->assign($list);

        return $this->fetch();
    }

    function stop()
    {
        $id = input('id', '-1');

        if ($id == -1) {
            ssh2("docker stop $({$this->canNotStopIds})");
        } else {
            ssh2("docker stop $id");
        }

        return $this->success('停止成功');
    }

    function start()
    {
        $id = input('id', '-1');
        if ($id == -1) {
            ssh2("docker start $({$this->canNotStopIds})");
        } else {
            ssh2("docker start " . $id);
        }
        return $this->success('启动成功');
    }

    function del()
    {
        $id = input('id', '-1');
        if ($id == -1) {
            ssh2("docker stop $({$this->canNotStopIds});docker rm $({$this->canNotStopIds})");
        } else {
            ssh2("docker stop {$id};docker rm {$id}");
        }
        return $this->success('删除容器成功');
    }
}
