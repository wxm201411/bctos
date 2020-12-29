#!/bin/bash
cd /bctos
docker=panel

tips(){
	echo '=================================================';
    echo -e "\033[32m $1 \033[0m"
}

tips "开始备份数据库"
root_pwd=$(grep 'MYSQL_ROOT_PASSWORD' Dockerfile|sed "s/ENV//;s/MYSQL_ROOT_PASSWORD//;s/=//;s/\\r//;s/\\n//;s/ //g")
docker exec ${docker} sh -c "exec mysqldump --all-databases -uroot -p${root_pwd}" > ~/"bctos_backup_"$nowTime".sql"
echo "备份完成"

tips "使用git下载并更新代码"
set -x
git config core.filemode false
git config --global user.email "panel@bctos.cn"
git config --global user.name "panel"

git stash
git pull
git stash pop
git stash drop
set +x

chmod +x /bctos/server/panel/entrypoint.sh
cd wwwroot/bctos.cn
chown -R 82.82 ./*
chmod -R +x scripts
find vendor -type d -name bin|xargs chmod -R +x
chmod -R 755 runtime db app
chmod -R 777 public

tips "更新数据库"
tag=$(git tag | awk 'END {print}')
docker exec panel sh -c "su - www-data -c 'cd /bctos/wwwroot/bctos.cn;vendor/bin/phinx migrate;php think update ${tag}'"

if [ -f update_${tag}.sh ];then
    tips "发现update_${tag}.sh文件，执行它"
    chmod +x update_${tag}.sh
    ./update_${tag}.sh
    rm -f update_${tag}.sh
fi
if [ -f update_${tag}.sql ];then
    tips "发现update_${tag}.sql，执行数据库导入,时间可能较长，请耐心等待"
docker exec -e MYSQL_PWD=$root_pwd -i ${docker} mysql -uroot << EOF
set global innodb_flush_log_at_trx_commit = 2;
set global sync_binlog = 2000;
EOF
    docker exec -i ${docker} sh -c "exec mysql -uroot -p${root_pwd} bctos_panel" < ./update_${tag}.sql
docker exec -e MYSQL_PWD=$root_pwd -i ${docker} mysql -uroot << EOF
set global innodb_flush_log_at_trx_commit = 1;
set global sync_binlog = 1;
EOF

    tips "导入数据库完成"
fi

rm -rf wwwroot/bctos.cn/runtime/*
tips "清空缓存完毕"

tips "备份数据库文件在："~/"bctos_backup_"$nowTime".sql"

list=$(git ls-files -u  | cut -f 2 | sort -u)
if [[ ${#list} != 0 ]];then
    echo -e  "\033[31m 重要：以下文件存在冲突，需要手工解决  \033[0m";
    git ls-files -u  | cut -f 2 | sort -u
fi

tips "升级更新完成"