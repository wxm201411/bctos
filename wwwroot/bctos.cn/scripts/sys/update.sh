#!/bin/bash
cd /bctos
docker=panel

Get_Ip_Address(){
	getIpAddress=""
	getIpAddress=$(curl -sS --connect-timeout 10 -m 60 https://www.bctos.cn/ip.php)
	if [ -z "${getIpAddress}" ] || [ "${getIpAddress}" = "0.0.0.0" ]; then
		isHosts=$(cat /etc/hosts|grep 'www.bctos.cn')
		if [ -z "${isHosts}" ];then
			echo "" >> /etc/hosts
			echo "47.106.212.16 www.bctos.cn" >> /etc/hosts
			getIpAddress=$(curl -sS --connect-timeout 10 -m 60 https://www.bctos.cn/ip.php)
			if [ -z "${getIpAddress}" ];then
				sed -i "/bctos.cn/d" /etc/hosts
			fi
		fi
	fi

	LOCAL_IP=$(ip addr | grep -E -o '[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}' | grep -E -v "^127\.|^255\.|^0\." | head -n 1)
}
tips(){
	echo '=================================================';
    echo -e "\033[32m $1 \033[0m"
}
tips "更新开始，先备份代码"
echo "代码目录："$(pwd)
nowTime=$(date +"%Y%m%d%H%M%S")
tar -zcf ~/"bctos_backup_"$nowTime".tar.gz" ./ --checkpoint=1000 --checkpoint-action=dot --totals -P

tips "开始备份数据库"
root_pwd=$(grep 'MYSQL_ROOT_PASSWORD' Dockerfile|sed "s/ENV//;s/MYSQL_ROOT_PASSWORD//;s/=//;s/\\r//;s/\\n//;s/ //g")
docker exec ${docker} sh -c "exec mysqldump --all-databases -uroot -p${root_pwd}" > ~/"bctos_backup_"$nowTime".sql"
echo "备份完成"

tips "使用git下载并更新代码"
git fetch --all
git reset --hard origin/master

tips "初始化代码目录和权限";
if [ ! -d "server/panel/mysql-data" ];then
	sudo mkdir -p server/panel/mysql-data
fi
sudo chmod +x /bctos/server/panel/entrypoint.sh
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
sudo chmod -R 755 runtime db app
sudo chmod -R 777 public

tips "替换配置文件中的密码";
Get_Ip_Address
#sed -i "s/123456/${MYSQL_PWD}/" config/database.php
sed -i "s/192\.168\.0\.8/${LOCAL_IP}/" config/weiphp_define.php
#sed -i "/SSH_PAWD/{s/123/${SSH_PAWD}/}" config/weiphp_define.php

tips "更新数据库"
tag=$(git tag | awk 'END {print}')
docker exec panel sh -c "su - www-data -c 'cd /bctos/wwwroot/bctos.cn;vendor/bin/phinx migrate;php think update ${tag}'"

rm -rf wwwroot/bctos.cn/runtime/*
tips "清空缓存完毕"


tips "备份代码文件在： "~/"bctos_backup_"$nowTime".tar.gz"
tips "备份数据库文件在："~/"bctos_backup_"$nowTime".sql"

tips "升级更新完成"