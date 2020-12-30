#!/bin/bash
old_host=$1
new_host=$2
user_name=$3
#多数据库支持
docker=${4:-"mysql57"}

#正式修改数据库中的密码
root_pwd=$(grep 'MYSQL_ROOT_PASSWORD' /bctos/server/${docker}/docker-compose.yml | sed -r 's/MYSQL_ROOT_PASSWORD://' | sed 's/\\r//;s/\\n//;s/ //g')
docker exec -e MYSQL_PWD=$root_pwd -i ${docker} mysql -uroot << EOF
use mysql;
UPDATE user SET Host='${new_host}' where User='${user_name}' and Host='${old_host}';
flush privileges;
EOF
