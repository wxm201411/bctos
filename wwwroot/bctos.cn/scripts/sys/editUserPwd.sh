#!/bin/bash
pwd=$1
name=$2
#多数据库支持
docker=${3:-"mysql57"}

cd /bctos/server/$docker
root_pwd=$(sed -n "/MYSQL_ROOT_PASSWORD/p" docker-compose.yml | sed -r "s/MYSQL_ROOT_PASSWORD://"| sed 's/ //g')
image=$(sed -n "/image:/p" docker-compose.yml | sed -r "s/image://"| sed 's/ //g')

#先判断镜像是否生成，如果还没生成则不需要修改容器
if [ ! -z $(docker images --format '{{.Repository}}:{{.Tag}}'| grep "$image") ];then
    # 存在镜像，接着判断容器是否在运行中，如果没有运行则先启动容器
    if [ -z $(docker ps -a --format '{{.Names}}'| grep $docker) ];then
        docker-compose up -d
    fi
    #进入容器查询账号
    res=$(docker exec -e MYSQL_PWD=$root_pwd -i ${docker} mysql -uroot << EOF
use mysql;
SELECT Host FROM user where User='${name}';
EOF
)
echo $res
res=$(echo $res|sed 's/Host//')
for host in $(echo $res)
do
    #进入容器修改密码
    docker exec -e MYSQL_PWD=$root_pwd -i ${docker} mysql -uroot << EOF
use mysql;
ALTER USER '${name}'@'${host}' IDENTIFIED WITH mysql_native_password BY '${pwd}';
flush privileges;
EOF
done

fi

if [[ $? && $name == "root" ]]; then
    #替换docker-compose.yml密码
    sed -i "/MYSQL_ROOT_PASSWORD/{s/${root_pwd}/${pwd}/}" docker-compose.yml
fi