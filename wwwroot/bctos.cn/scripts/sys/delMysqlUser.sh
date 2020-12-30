#!/bin/bash
name=$1
host=$2
#多数据库支持
docker=${3:-"mysql57"}

#正式修改数据库中的密码
root_pwd=$(grep 'MYSQL_ROOT_PASSWORD' /bctos/server/${docker}/docker-compose.yml | sed -r 's/MYSQL_ROOT_PASSWORD://' | sed 's/\\r//;s/\\n//;s/ //g')
docker exec -e MYSQL_PWD=$root_pwd -i ${docker} mysql -uroot << EOF
drop user '${name}'@'${host}';
flush privileges;
EOF
