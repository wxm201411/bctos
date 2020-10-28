#!/bin/bash
cd $(dirname $0)
cd ../../
site_path=$(pwd)

conf=$1
path=$2
port=$3

#创建网站目录
if [ ! -d $path ]; then
    mkdir -p $path
fi
cd $path

#增加默认网站文件
cp -r "$site_path/public/init_site/*" ./
chown -R 82.82 $path
chmod -R 755 $path

#增加nginx配置
cp $conf /www/conf.d/

#增加nginx容器的端口

#重启nginx
docker exec -it nginx nginx -s reload



