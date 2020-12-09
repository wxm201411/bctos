#!/bin/bash
doamin=$1
verson=$2
#多nginx版本支持
docker=${3:-"nginx"}

conf=/bctos/server/${docker}/conf.d/${doamin}".conf"
row=$[ $(sed -n "/fastcgi_pass/=" $conf)-1 ]
if [ $verson == '0' ];then
    #关闭PHP
    sed -i "${row},+5s/^/#/g" $conf
else

    #开启PHP
    sed -i "${row},+5s/^#//g" $conf

    #切换PHP版本
    sed -i -r "s/fastcgi_pass[ \t]+.*9000/fastcgi_pass ${verson}:9000/" $conf
fi

#重启nginx
docker exec ${docker} nginx -s reload
