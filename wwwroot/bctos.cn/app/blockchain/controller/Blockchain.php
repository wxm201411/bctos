<?php

namespace app\blockchain\controller;

use app\common\controller\WebBase;

//PC运营管理端的控制器
class Blockchain extends WebBase
{
    function lists()
    {
        ssh2('bash -l ' . SITE_PATH . '/scripts/startSocket.sh');
        $this->assign('page_tips', '网络启动后会生成两个peer节点和一个orderer节点，并且自动应用通道，通道名称可在上面的“功能配置”中设置');

        return $this->fetch();
    }

    function deploy()
    {
        if (IS_POST) {
            $post = input('post.');
            S('deploy_post_' . $this->mid, $post);

            $config = getAppConfig('blockchain');
            $data['auth'] = $config['auth'];
            $data['channel'] = $config['channel'];
            $data = array_merge($data, $post);

            $commond = ['data' => 'deploy', 'param' => implode(' ', $data)];
            $this->assign('commond', json_encode($commond));
            return $this->fetch('terminal');
        } else {
            $type = input('type', 'last');
            if ($type == 'default') {
                $this->assign('data', ['lang' => 'golang', 'path' => '$GOPATH/src/github.com/hyperledger/fabric-samples/chaincode/fabcar/go', 'name' => 'fabcar']);
            } else {
                $data = S('deploy_post_' . $this->mid);
                if (!$data) {
                    $data = ['lang' => 'golang', 'path' => '', 'name' => ''];
                }
                $this->assign('data', $data);
            }

            return $this->fetch();
        }
    }

    function test()
    {
        if (IS_POST) {
            $post = input('post.');
            $post['param'] = json_encode(json_decode($post['param']));//重新组装json,去掉不必要的空格，生成标准的json
            D('common/History')->saveData($this->mid, $post);
            $config = getAppConfig('blockchain');

            $commond = 'bash -l ' . SITE_PATH . "/scripts/fabric2/execCC.sh {$post['func']} {$post['chaincode']} '{$post['param']}' {$config['channel']}";
            $res = ssh2($commond, true);
            return $this->success($res);
        } else {
            $codeList = [];
            $content = ssh2('bash -l ' . SITE_PATH . '/scripts/fabric2/queryCC.sh');
            //dump($content);
            if (empty($content)) exit('请先安装链码');
            $arr = wp_explode($content, "\n");
            foreach ($arr as $a) {
                $labelArr = explode(", Label: ", $a);
                isset($labelArr[1]) && $codeList[] = $labelArr[1];
            }
            if (empty($codeList)) exit('请先安装链码!');
            $this->assign('codeList', $codeList);

            $history = D('common/History')->getList($this->mid);
            $this->assign('history', $history);
            $data = isset($history[0]) ? $history[0] : [];
            if (!$data) {
                $data = ['chaincode' => '', 'func' => 'invoke', 'param' => ''];
            }
            $this->assign('data', $data);

            return $this->fetch();
        }
    }

    function delHistory()
    {
        D('common/History')->del(input('id/d'));
    }

    function start()
    {
        $config = getAppConfig('blockchain');
        $commond = ['data' => 'start_net', 'param' => $config['auth'] . ' ' . $config['channel']];
        $this->assign('commond', json_encode($commond));
        return $this->fetch('terminal');
    }

    function stop()
    {
        $commond = ['data' => 'close_net'];
        $this->assign('commond', json_encode($commond));
        return $this->fetch('terminal');
    }
}
