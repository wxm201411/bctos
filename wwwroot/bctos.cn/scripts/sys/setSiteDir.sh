#!/bin/bash
cd $(dirname $0)
cd ../../
site_path=$(pwd)

conf=$1
path=$2
open_basedir=$3
recode_log=$4
#多nginx版本支持
docker=${5:-"nginx"}

old_path=$(sed -n '/ root /p' /bctos/server/${docker}/conf.d/$conf | sed -r 's:[ \t]+root[ \t]+/bctos/wwwroot/::' | sed 's/;//' | sed 's/\r//')
old_site=$(echo $old_path|sed 's/\/.*$//g') #只取网站根目录
#网站目录更改
if [ $2 != '-' ];then
    new_site=$(echo $path|sed 's/\/.*$//g') #只取网站根目录
    #如果目录不存在，直接把旧目录名修改新目录名，否则直接指向新目录，旧目录保留
    if [ ! -d $new_site ]; then
        mv /bctos/wwwroot/${old_site} /bctos/wwwroot/${new_site}
    fi

    #修改nginx配置文件
    sed -r -i "/[ \t]+root[ \t]+/{s:${old_path}:${2}:}" /bctos/server/${docker}/conf.d/$conf

    old_site=$new_site
fi
#防跨站攻击
if [ $open_basedir != '-' ];then
    if [ $open_basedir == '1' ];then
        echo "open_basedir=/bctos/wwwroot/$old_site/:/tmp/" > /bctos/wwwroot/$old_site/.user.ini
        chmod 777 /bctos/wwwroot/$old_site/.user.ini
        else
        rm -f /bctos/wwwroot/$old_site/.user.ini
    fi
fi

if [ $recode_log != '-' ];then
    if [ $recode_log == '1' ];then
    echo /bctos/server/${docker}/conf.d/$conf
        sed -r -i '/access_log.*\.log/{s/^#//}' /bctos/server/${docker}/conf.d/$conf
        else
        sed -r -i '/access_log.*\.log/{s/^/#/}' /bctos/server/${docker}/conf.d/$conf
    fi
fi
#重启nginx
docker restart ${docker}