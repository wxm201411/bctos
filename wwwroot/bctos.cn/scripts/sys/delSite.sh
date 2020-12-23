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

#删除日志
conf_file=/bctos/server/${docker}/conf.d/$conf
rm -rf $(sed -r -n '/access_log.*\.log/p' $conf_file|sed 's/access_log//;s/;//;s/^\s*//;s/\s*$//')
rm -rf $(sed -r -n '/error_log.*\.log/p' $conf_file|sed 's/error_log//;s/;//;s/^\s*//;s/\s*$//')

#删除nginx配置
rm -rf $conf_file
rewrite_file=$(echo $conf|sed 's/conf$/rewrite.conf/')
rm -rf /bctos/server/${docker}/rewrite/$rewrite_file

#重启nginx
docker restart ${docker}