<?php

namespace app\blockchain\controller;

use app\common\controller\WebBase;

//PC运营管理端的控制器
class Dev extends WebBase
{
    var $log1, $log2, $log3;

    function initialize()
    {
        parent::initialize();

        $fabric2_status = trim(ssh2('bash -l ' . SITE_PATH . '/scripts/fabric2/dev_status.sh'));
        if ($fabric2_status == 'not install') {
            return $this->error('需要先安装Fabric', U('blockchain/blockchain/lists'));
        }
        $this->assign('fabric2_status', $fabric2_status);

        $this->log1 = 'bc_web_log_dev1_' . $this->mid;
        $this->log2 = 'bc_web_log_dev2_' . $this->mid;
        $this->log3 = 'bc_web_log_dev3_' . $this->mid;
    }

    function lists()
    {
        return $this->fetch();
    }

    //终端1
    function terminal_1()
    {
        return $this->fetch();
    }

    //终端2
    function terminal_2()
    {
        return $this->fetch('terminal_1');
    }

    //终端3
    function terminal_3()
    {
        return $this->fetch('terminal_1');
    }

    function deploy()
    {
        if (IS_POST) {
            $post = input('post.');
            S('deploy_post_' . $this->mid, $post);
        } else {
            $type = input('type', 'last');
            if ($type == 'default') {
                $this->assign('data', ['lang' => 'golang', 'path' => 'fabcar/go', 'name' => 'fabcar']);
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
        $content = '';
        if (IS_POST) {
            $post = input('post.');
            S('deploy_test_' . $this->mid, $post);

            $config = getAppConfig('blockchain');
            $post['auth'] = $config['auth'];
            $post['channel'] = $config['channel'];
            $post['param'] = "'{$post['param']}'";


            $param = http_build_query($post);
            //$content = $this->goGet('fabric2/execCC', $param);
            return $this->success($content);
        } else {
            $codeList = [];

            //$content = $this->goGet('fabric2/QueryCC');
            if (empty($content)) exit('请先安装链码');
            $arr = wp_explode($content, "\n");
            foreach ($arr as $a) {
                $labelArr = explode(", Label: ", $a);
                isset($labelArr[1]) && $codeList[] = $labelArr[1];
            }
            if (empty($codeList)) exit('请先安装链码!');
            $this->assign('codeList', $codeList);

            $data = S('deploy_test_' . $this->mid);
            if (!$data) {
                $data = ['chaincode' => '', 'func' => 'invoke', 'param' => ''];
            }
            $this->assign('data', $data);

            return $this->fetch();
        }
    }

    function web_msg($content, $log)
    {
        // 指明给谁推送，为空表示向所有在线用户推送
        $to_uid = $this->mid;

        // 推送的url地址，使用自己的服务器地址
        $push_api_url = "http://127.0.0.1:2121/";
        $post_data = array(
            "type" => "publish",
            "content" => json_url(['content' => $content, 'log' => $log]),
            "to" => $to_uid,
        );
        add_debug_log($post_data, 'post2');

        //dump($post_data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $push_api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:"));
        $return = curl_exec($ch);
        curl_close($ch);
        //dump($return);
    }
}
