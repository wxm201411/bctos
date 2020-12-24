#!/bin/bash
cd /bctos

showMsg(){
    echo "########################### $1 ###########################"
}
showMsg "更新代码"
git fetch --all
git reset --hard origin/master

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