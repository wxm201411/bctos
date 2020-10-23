#!/bin/bash
if [ -f "/var/www/html/vendor/web-msg-sender/start.php" ];then
    php /var/www/html/vendor/web-msg-sender/start.php start -d
    php /var/www/html/public/node/server/bin/websocket.php -d &
fi