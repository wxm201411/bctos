#!/bin/bash
# 需要用的软件，如：installSoft git
function installSoft(){
    if [[  ! $(which $1) ]]; then
        echo "============Install $1 begin================================="
        yum -y install $1
        echo "============Install $1 end, return status: $?==================="
    fi
}
# 启动容器
function startServer(){
    if [[ $1 != 'not' ]] && [[ $1 != '' ]] && [ -d /bctos/server/$1 ] && [ -z $(docker ps --format '{{.Names}}'|grep $1) ];then
        cd /bctos/server/$1
        docker-compose up -d
     fi
}
# 重启容器
function restartServer(){
    if [[ $1 != 'not' ]] && [[ $1 != '' ]] && [ -z $(docker ps --format '{{.Names}}'|grep $1) ];then
        cd /bctos/server/$1
        if [ -z $(docker ps --format '{{.Names}}'|grep $1) ];then
            docker-compose up -d
        else
            docker-compose restart
        fi
     fi
}
# 更新nginx配置
function nginxReload(){
    cd /bctos/server/nginx
    [ -z $(docker ps --format '{{.Names}}'|grep nginx) ] && docker-compose up -d || docker exec nginx nginx -s reload
}



