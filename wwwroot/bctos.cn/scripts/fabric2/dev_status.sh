#!/bin/bash
#if ! [[ $(ps aux|grep 'web-msg-sender/start.php'|grep -vc grep) -gt 0 ]]; then
#  php /www/wwwroot/bctos.cn/vendor/web-msg-sender/start.php start -d
#fi
#if ! [[ $(ps aux|grep 'web-msg-sender/start_log.php'|grep -vc grep) -gt 0 ]]; then
#  php /www/wwwroot/bctos.cn/vendor/web-msg-sender/start_log.php &
#fi

if ! [[ $(command -v peer) ]]; then
  echo "not install"
  exit
fi

check=$((docker ps | grep "fabric-ccenv" | wc -l) 2>null)
if [[ check -eq 0 ]]; then
  echo "not running"
  exit
fi

echo "is running"