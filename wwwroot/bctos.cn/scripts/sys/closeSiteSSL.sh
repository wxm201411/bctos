#!/bin/bash
doamin=$1
open=$2
#多nginx版本支持
docker=${3:-"nginx"}

conf=/bctos/server/${docker}/conf.d/${doamin}".conf"

row=$(sed -n "/ssl_/=" $conf)
if [ ! $row ];then
    echo "请安装证书后再试" >&2;
    exit 1;
fi

if [ $open == "1" ]; then
    # 开启
    sed -i -r 's/#listen[ \t]+443/listen 443' $conf
    sed -i 's/# ssl_/ ssl_' $conf
else
    # 关闭
    sed -i -r 's/listen[ \t]+443/#listen 443' $conf
    sed -i 's/ ssl_/#ssl_' $conf
fi
#重启nginx
docker restart ${docker}