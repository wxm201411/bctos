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

row=$(sed -n "/HTTPS_FORCE_START/=" $conf)
if [ $open == "1" ]; then
    # 开启
    if [[ $row > 0 ]];then
        sed -i "${row},+4s/^#//g" $conf
    else
        sed -i -r '/[ \t]+index[ \t]+/a\ \
        #HTTPS_FORCE_START \
        if ($server_port !~ 443){ \
            rewrite ^(/.*)$ https://$host$1 permanent; \
        } \
        #HTTPS_FORCE_END    \
        ' $conf
    fi
else
    # 关闭
    sed -i "${row},+4s/^/#/g" $conf
fi
#重启nginx
docker restart ${docker}