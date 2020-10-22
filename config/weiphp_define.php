<?php
// 异常错误报错级别,
// error_reporting(E_ERROR | E_PARSE);
error_reporting(E_ALL);

define('SITE_PATH', dirname(dirname(__FILE__)));
define('DEFAULT_PBID', 1);
define('DEFAULT_WPID', 1);

// 单账号模式
//define('WPID', 1);
//单客户端模式
//define('PBID', 73);
//是否显示官网首页
define('HAS_INDEX', false);

define('_PHP_FILE_', rtrim($_SERVER['SCRIPT_NAME'], DIRECTORY_SEPARATOR));
$_root = dirname(_PHP_FILE_);
define('__ROOT__', (($_root == '/' || $_root == '\\' || $_root == '\/') ? '' : $_root));
if (!defined('HTTP_PREFIX')) {
    $isSecure = false;
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
        $isSecure = true;
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
        $isSecure = true;
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
        $isSecure = true;
    } elseif (isset($_SERVER['HTTP_X_CLIENT_PROTO']) && $_SERVER['HTTP_X_CLIENT_PROTO'] == 'https') {
        $isSecure = true;
    } elseif (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') {
        $isSecure = true;
    }

    define('HTTP_PREFIX', $isSecure ? 'https://' : 'http://');
}
define('SITE_DOMAIN', strip_tags(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost')); // 运行socket时，获取不到http_host
define('SITE_URL', HTTP_PREFIX . SITE_DOMAIN . __ROOT__);
define('SITE_DIR_NAME', str_replace('.', '_', pathinfo(SITE_PATH, PATHINFO_BASENAME))); // 网站目录名，通常用于缓存，session,cookie的前缀，以防止多网站里数据冲突

define('DATA_PATH', SITE_PATH . '/runtime/data/'); // 应用数据目录
define('UPLOAD_PATH', SITE_PATH . '/public/storage');
define('SHOP_STOCK_TIME', 1800); // 商城库存锁定时间，默认30分钟

define('SHOP_EVENT_TYPE', 0);
define('COLLAGE_EVENT_TYPE', 1);
define('SECKILL_EVENT_TYPE', 2);
define('HAGGLE_EVENT_TYPE', 3);
define('AUCTION_EVENT_TYPE', 4);

define('MODEL_INSERT', 1); // 插入模型数据
define('MODEL_UPDATE', 2); // 更新模型数据
define('MODEL_BOTH', 3);// 包含上面两种方式

define('REMOTE_BASE_URL', 'http://localhost/xiaowei/public');

//只有PC版,不需要公众号
define('ONLY_WEB', true);
//golang 服务端的地址
define('SSH_PAWD', '123');
define('SSH_IP', '192.168.0.8');

//小韦云链的git仓库
define('GITEE_URL', 'https://gitee.com/bctos_cn/bctos/tags');
define('GITHUB_URL', 'https://github.com/wxm201411/bctos/tags');
