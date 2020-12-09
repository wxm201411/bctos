#!/bin/bash
db_name=$1
file=$2
cron_id=$3
#多数据库支持
docker=${4:-"mysql57"}

if [[  !($(which zip) && $(zip -v)) ]]; then
    yum -y install unzip zip > /dev/null
fi
root_pwd=$(grep 'MYSQL_ROOT_PASSWORD' /bctos/server/${docker}/docker-compose.yml | sed -r 's/MYSQL_ROOT_PASSWORD://' | sed 's/ //g')
cd /bctos/wwwroot/bctos.cn/public/storage/backup/$cron_id
#set -x
unzip -o ${file}.zip
docker exec -i  ${docker} sh -c "exec mysql -uroot -p${root_pwd} ${db_name}" < ./$file
rm -f ${file}
#set +x