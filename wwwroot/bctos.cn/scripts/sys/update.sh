#!/bin/bash
cd /bctos

showMsg(){
    echo "######## $1 ########"
}
showMsg "更新开始，先备份代码"
echo "代码目录："$(pwd)
nowTime=$(date +"%Y%m%d%H%M%S")
tar -zcf ~/"bctos_backup_"$nowTime".tar.gz" ./ --checkpoint=1000 --checkpoint-action=dot --totals -P

showMsg "开始备份数据库"
export MYSQL_PWD=$2
mysqldump -u$1 -h$3 -P$4 $5 > ~/"bctos_backup_"$nowTime".sql"
echo "备份完成"

showMsg "使用git下载并更新代码"
git pull

showMsg "更新数据库"
tag=$(git tag | awk 'END {print}')
docker exec panel sh -c "cd /bctos/wwwroot/bctos.cn;vendor/bin/phinx migrate;php think update ${tag}"

rm -rf wwwroot/bctos.cn/runtime/*
echo "清空缓存完毕"


echo "备份代码文件在： "~/"bctos_backup_"$nowTime".tar.gz"
echo "备份数据库文件在："~/"bctos_backup_"$nowTime".sql"

showMsg "升级更新完成"
