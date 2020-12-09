#!/bin/bash
root_pwd=$1
old_host=$2
new_host=$3
user_name=$4
#多数据库支持
docker=${5:-"mysql57"}

#正式修改数据库中的密码
docker exec -e MYSQL_PWD=$root_pwd -i ${docker} mysql -uroot << EOF
use mysql;
UPDATE user SET Host='${new_host}' where User='${user_name}' and Host='${old_host}';
flush privileges;
EOF
