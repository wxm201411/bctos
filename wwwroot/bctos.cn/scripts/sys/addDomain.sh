#!/bin/bash
conf=$1
domain=$2
#多nginx版本支持
docker=${3:-"nginx"}

sed -i "s/server_name/server_name $domain/g" /bctos/server/${docker}/conf.d/$conf
#重启nginx
docker restart ${docker}