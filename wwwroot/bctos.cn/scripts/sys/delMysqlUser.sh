#!/bin/bash
root_pwd=$1
name=$2
host=$3
#多数据库支持
docker=${4:-"mysql57"}

#正式修改数据库中的密码
docker exec -e MYSQL_PWD=$root_pwd -i ${docker} mysql -uroot << EOF
drop user '${name}'@'${host}';
flush privileges;
EOF
