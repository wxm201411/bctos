#!/bin/bash
cd /bctos/server/$1

if [[ ! -f "docker-compose.yml" ]];then
    #判断是否已经下载安装包，否则先下载并解压到server目录
    wget https://www.bctos.cn/server/${1}.zip
    unzip ${1}.zip
fi
docker-compose up -d




