#!/bin/bash
conf=$1
serach=$2
replace=$3
#多nginx版本支持
docker=${4:-"nginx"}

sed -i "s/bctos\/wwwroot\/$serach/bctos\/wwwroot\/$replace/g" /bctos/server/${docker}/conf.d/$conf
#重启nginx
docker restart ${docker}



