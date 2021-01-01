#!/bin/sh

# 后台启动
/usr/sbin/php-fpm7 -D

# 开启web-msg-sender
php /bctos/wwwroot/bctos.cn/vendor/web-msg-sender/start.php start -d

# 开启websocket
php /bctos/wwwroot/bctos.cn/public/node/server/bin/websocket.php &

# 关闭后台启动，hold住进程
/usr/sbin/nginx -g 'daemon off;'
