#!/bin/bash
doamin=$1
#多nginx版本支持
docker=${2:-"nginx"}

domain_path=/bctos/server/${docker}/conf.d/cert/${doamin}

# 写入到nginx配置文件
conf=$domain".conf"

check=$(egrep "listen[ \t]+443" /bctos/server/${docker}/conf.d/$conf)
if [[ $check ]]; then
    #删除443端口
	sed -i -r '/listen[ \t]+443/d' /bctos/server/${docker}/conf.d/$conf
fi

check=$(grep "$domain_path/ssl.key" /bctos/server/${docker}/conf.d/$conf)
if [[ ! $check ]]; then
    #删除证书配置
    sed -i -r '/[ \t]ssl_/d' /bctos/server/${docker}/conf.d/$conf
fi

#重启nginx
docker restart ${docker}



