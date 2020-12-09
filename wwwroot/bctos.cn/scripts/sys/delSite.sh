#!/bin/bash
conf=$1
path=$2
#多nginx版本支持
docker=${3:-"nginx"}

check='..'
if [[ $conf == *$check* || $path == *$check* ]];then
    echo "非法输入"
    exit 1
fi
#删除网站目录
rm -rf /bctos/wwwroot/$path

#删除nginx配置
rm -rf /bctos/server/${docker}/conf.d/$conf
rm -rf /bctos/server/${docker}/rewrite/$conf

#重启nginx
docker restart ${docker}