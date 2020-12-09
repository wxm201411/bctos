#!/bin/bash
conf=$1
domain=$2
#多nginx版本支持
docker=${3:-"nginx"}

sed -i "0,/$domain/s///" /bctos/server/${docker}/conf.d/$conf
#重启nginx
docker restart ${docker}