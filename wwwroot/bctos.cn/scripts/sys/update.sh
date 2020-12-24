#!/bin/bash
cd /bctos
docker=panel

showMsg(){
    echo "######## $1 ########"
}
showMsg "更新开始，先备份代码"
echo "代码目录："$(pwd)
nowTime=$(date +"%Y%m%d%H%M%S")
tar -zcf ~/"bctos_backup_"$nowTime".tar.gz" ./ --checkpoint=1000 --checkpoint-action=dot --totals -P

showMsg "开始备份数据库"
root_pwd=$(grep 'MYSQL_ROOT_PASSWORD' Dockerfile|sed "s/ENV//;s/MYSQL_ROOT_PASSWORD//;s/=//;s/ //g")
docker exec ${docker} sh -c "exec mysqldump --all-databases -uroot -p${root_pwd}" > ~/"bctos_backup_"$nowTime".sql"
echo "备份完成"

showMsg "使用git下载并更新代码"
git fetch --all
git reset --hard origin/master

if [ ! -d "server/panel/mysql-data" ];then
	sudo mkdir -p server/panel/mysql-data
fi
sudo chmod +x /bctos/server/panel/entrypoint.sh
sudo chmod -R 777 /bctos/wwwroot/bctos.cn/public/kod/
cd wwwroot/bctos.cn
if [ ! -d runtime ];then
	sudo mkdir runtime
fi
if [ ! -d "db/migrations" ];then
	sudo mkdir -p db/migrations
fi
if [ ! -d "public/storage" ];then
	sudo mkdir -p public/storage
fi
#在容器中82表示www-data用户
if [ -z $(cat /etc/passwd|grep www-data) ];then
    tips "增加www-data用户";
    sudo groupadd -g 82 www-data
    sudo useradd -u 82 -g 82  www-data
fi

if [ ! -f "config/.ssh/id_rsa" ];then
    tips "生成SSH2需要的密钥";

    mkdir -p config/.ssh
    mkdir -p /root/.ssh
    ssh-keygen -f config/.ssh/id_rsa -N bctos
    cp config/.ssh/id_rsa.pub /root/.ssh/
    sudo chown -R 82.82 config/.ssh
    chmod -R 755 config/.ssh

    cat /root/.ssh/id_rsa.pub >> /root/.ssh/authorized_keys
    chmod 600 /root/.ssh/authorized_keys
    chmod 700 /root/.ssh
fi

sudo chown -R 82.82 ./*
sudo chmod -R +x scripts
sudo find vendor -type d -name bin|xargs chmod -R +x
sudo chmod -R 755 public runtime db app

showMsg "更新数据库"
tag=$(git tag | awk 'END {print}')
docker exec panel sh -c "su - www-data -c 'cd /bctos/wwwroot/bctos.cn;vendor/bin/phinx migrate;php think update ${tag}'"

rm -rf wwwroot/bctos.cn/runtime/*
echo "清空缓存完毕"


echo "备份代码文件在： "~/"bctos_backup_"$nowTime".tar.gz"
echo "备份数据库文件在："~/"bctos_backup_"$nowTime".sql"

showMsg "升级更新完成"
