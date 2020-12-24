#!/bin/bash
#多数据库支持
docker=${1:-"mysql57"}
pwd=$2
name=$3
host=$4

cd /bctos/server/$docker

root_pwd=$(sed -n "/MYSQL_ROOT_PASSWORD/p" docker-compose.yml | sed -r "s/.*://"|sed 's/\\r//;s/\\n//;s/ //g')

#正式修改数据库中的密码
docker exec -e MYSQL_PWD=$root_pwd -i ${docker} mysql -uroot << EOF
CREATE USER '${name}'@'${host}' IDENTIFIED WITH mysql_native_password BY '${pwd}';
GRANT ALL ON ${name}.* TO '${name}'@'${host}';
flush privileges;
EOF