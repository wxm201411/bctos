#!/bin/bash
path=$1
file=$2
backup=/bctos/wwwroot/bctos.cn/public/storage/backup
if [[  !($(which zip) && $(zip -v)) ]]; then
    yum -y install unzip zip > /dev/null
fi

if [ ! -d $backup ]; then
    mkdir -p $backup
    chown -R 82.82 $backup
fi

cd $path
zip -r ${file} ./* -x $backup > /dev/null
mv ${file} $backup/${file}
cd $backup
chmod -R 755 ./*
ls -lh ${file} | awk '{print $5}'
