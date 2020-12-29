#!/bin/bash
cd $(dirname $0)
cd ../../
site_path=$(pwd)

conf=$1
path=$2
port=$3
#多nginx版本支持
docker=${4:-"nginx"}

#创建网站目录
if [ ! -d $path ]; then
    mkdir -p $path
fi
cd $path

#增加默认网站文件
cp -R "$site_path/public/init_site/." ./
chown -R 82.82 $path
chmod -R 755 $path

#防跨站攻击
echo "open_basedir=$old_path/:/tmp/" > $path/.user.ini

#增加nginx配置
cp $conf /bctos/server/${docker}/conf.d/

#创建伪静态文件
rewrite_file=$(basename $conf|sed 's/\.conf/.rewrite.conf/')
touch /bctos/server/${docker}/rewrite/${rewrite_file}
chmod 777 /bctos/server/${docker}/rewrite/${rewrite_file}

#重启nginx
docker restart ${docker}



