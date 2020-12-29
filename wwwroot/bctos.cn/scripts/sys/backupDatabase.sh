#!/bin/bash
root_pwd=$1
db_name=$2
file=$3
#多数据库支持
docker=${4:-"mysql57"}

if [[  !($(which zip) && $(zip -v)) ]]; then
    yum -y install unzip zip > /dev/null
fi

if [ ! -d "/bctos/wwwroot/bctos.cn/public/storage/backup" ]; then
    mkdir -p /bctos/wwwroot/bctos.cn/public/storage/backup
    chown -R 82.82 /bctos/wwwroot/bctos.cn/public/storage/backup
fi
cd /bctos/wwwroot/bctos.cn/public/storage/backup/
docker exec -e MYSQL_PWD=$root_pwd -i ${docker} mysql -uroot << EOF
set global innodb_flush_log_at_trx_commit = 2;
set global sync_binlog = 2000;
EOF
    docker exec ${docker} sh -c "exec mysqldump -uroot -p${root_pwd} ${db_name}" > ${file}
docker exec -e MYSQL_PWD=$root_pwd -i ${docker} mysql -uroot << EOF
set global innodb_flush_log_at_trx_commit = 1;
set global sync_binlog = 1;
EOF

zip ${file}.zip ${file} > /dev/null
rm -f ${file}
chmod -R 777 ./*
ls -lh ${file}.zip | awk '{print $5}'