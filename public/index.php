<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;

header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:*');
// 响应头设置
header('Access-Control-Allow-Headers:*');

if (version_compare ( PHP_VERSION, '7.1.0', '<' ))
    die ( 'Your PHP Version is ' . PHP_VERSION . ', But 小韦云 require PHP >= 7.1.0 !' );

/**
 * 微信接入验证
 * 在入口进行验证而不是放到框架里验证，主要是解决验证URL超时的问题
 */
define('SYSTEM_TOKEN', 'weiphp');

define('WORKERMAN_URL', 'localhost');

if (!empty($_GET['echostr']) && !empty($_GET["signature"]) && !empty($_GET["nonce"])) {
    $signature = $_GET["signature"];
    $timestamp = $_GET["timestamp"];
    $nonce = $_GET["nonce"];

    $tmpArr = array(
        SYSTEM_TOKEN,
        $timestamp,
        $nonce
    );
    sort($tmpArr, SORT_STRING);
    $tmpStr = sha1(implode($tmpArr));

    if ($tmpStr == $signature) {
        echo $_GET["echostr"];
    }
    exit();
}
//判断是否安装过weiphp
//if (!is_file(__DIR__.'/storage/install.lock')) {
    //header('Location: ./install.php');
    //exit ();
//}

require __DIR__ . '/../vendor/autoload.php';

// 执行HTTP应用并响应
$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);
