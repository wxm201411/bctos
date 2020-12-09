#!/bin/bash
file=$1
path=$2

if [[  !($(which zip) && $(zip -v)) ]]; then
    yum -y install unzip zip > /dev/null
fi

unzip -o /bctos/wwwroot/bctos.cn/public/storage/backup/${file} -d $path > /dev/null
chown -R 82.82 $path
chmod -R 755 $path
echo 1