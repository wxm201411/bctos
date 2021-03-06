#!/bin/bash
LANG=en_US.UTF-8

#MYSQL_PWD=${1:-''}
#if [ ! $MYSQL_PWD ];then
#	MYSQL_PWD=$(head /dev/urandom |cksum |md5sum |cut -c 1-9)
#fi

# 需要用的软件
function installSoft(){
    if [[  !($(which $1) && $($1 --version)) ]]; then
        echo "============Install $1 begin================================="
        sudo apt-get -y install $1
        echo "============Install $1 end, return status: $?==================="
    fi
}
error_tips(){
	echo '=================================================';
	echo -e  "\033[31m $1  \033[0m";
	GetSysInfo
	exit 1
}
tips(){
	echo '=================================================';
    echo -e "\033[32m $1 \033[0m"
}

tips "
+----------------------------------------------------------------------
| 小韦云面板支持 CentOS/Ubuntu/Debian 系统
+----------------------------------------------------------------------
| Copyright © 2020-2099 小韦云科技(https://www.bctos.cn) All rights reserved.
+----------------------------------------------------------------------
| 小韦云面板安装成功后可通过 http://你的服务器IP:666 进行访问.
+----------------------------------------------------------------------
"
SSH_PAWD=''
function inputPwd(){
    tips "面板需要使用服务器的root账号来部署网站或文件管理"
    read -s -p "请输入root密码：" SSH_PAWD
    echo -e ""
    read -s -p "再次输入root密码：" SSH_PAWD2
    echo -e ""
    if [[ $SSH_PAWD != $SSH_PAWD2 ]];then
        tips "两次输入的密码不相同，请重试"
        inputPwd
    fi
}
inputPwd

GetSysInfo(){
	if [ -s "/etc/redhat-release" ];then
		SYS_VERSION=$(cat /etc/redhat-release)
	elif [ -s "/etc/issue" ]; then
		SYS_VERSION=$(cat /etc/issue)
	fi
	SYS_INFO=$(uname -a)
	SYS_BIT=$(getconf LONG_BIT)
	MEM_TOTAL=$(free -m|grep Mem|awk '{print $2}')
	CPU_INFO=$(getconf _NPROCESSORS_ONLN)

	echo -e ${SYS_VERSION}
	echo -e Bit:${SYS_BIT} Mem:${MEM_TOTAL}M Core:${CPU_INFO}
	echo -e ${SYS_INFO}
	echo -e "请截图以上报错信息向官方QQ群求助：884210423"
}

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

is64bit=$(getconf LONG_BIT)
if [ "${is64bit}" != '64' ];then
	error_tips "抱歉, 系统不支持32位系统, 请使用64位系统或安装小韦云链!";
fi
#check=$(lsof -i :$NGINX_PORT | awk 'END {print $1}')
#if [ $check ]; then
#	error_tips "nginx的端口号： $NGINX_PORT 已被占用，占用的服务是：$check，请关闭此服务或换端口再试";
#fi
#check=$(lsof -i :$MYSQL_PORT | awk 'END {print $1}')
#if [ $check ]; then
#	error_tips "mysql的端口号： $MYSQL_PORT 已被占用，占用的服务是：$check，请关闭此服务或换端口再试";
#fi
#check=$(lsof -i :$PHP_PORT | awk 'END {print $1}')
#if [ $check ]; then
#	error_tips "php的端口号： $(PHP_PORT) 已被占用，占用的服务是：$(check)，请关闭此服务或换端口再试";
#fi

if [ ! -d "/bctos" ]; then
	mkdir /bctos
fi	
cd /bctos

which -v
if [ $? -ne 0 ]; then
    tips "安装which"
	sudo apt-get -y install which
fi
installSoft wget
installSoft curl
installSoft git

if [[  $(which podman) ]]; then
    tips "移除podman"
    sudo apt-get -y remove podman
fi

tips "从gitee下载代码"
if [ -d wwwroot ];then
	git pull
else
	git clone https://gitee.com/bctos_cn/bctos.git ./
	if [ $? -ne 0 ]; then
	    tips "gitee下载失败，尝试从github下载代码"
		git clone https://github.com/wxm201411/bctos.git ./
	fi
fi
if [[ ! -d "wwwroot" ]]; then
	    error_tips "代码下载失败，请检查服务器是否连接外网"
	else
		tips "代码下载成功"
fi
tips "设置防火墙，让容器可以访问外网"
firewall-cmd --zone=public --add-masquerade --permanent
firewall-cmd --reload

# 安装docker
function install_docker(){
	tips "安装docker软件"
    # step 1: 安装必要的一些系统工具
    sudo apt-get update
    sudo apt-get -y install apt-transport-https ca-certificates curl software-properties-common
    # step 2: 安装GPG证书
    curl -fsSL https://mirrors.aliyun.com/docker-ce/linux/ubuntu/gpg | sudo apt-key add -
    # Step 3: 写入软件源信息
    sudo add-apt-repository "deb [arch=amd64] https://mirrors.aliyun.com/docker-ce/linux/ubuntu $(lsb_release -cs) stable"
    # Step 4: 更新并安装Docker-CE
    sudo apt-get -y update
    sudo apt-get -y install docker-ce=19.03.12 docker-ce-cli=19.03.12 containerd.io

    sudo mkdir -p /etc/docker
    sudo tee /etc/docker/daemon.json <<-'EOF'
{
  "registry-mirrors": ["https://u1fynok0.mirror.aliyuncs.com"]
}
EOF
    sudo systemctl daemon-reload
    sudo systemctl restart docker
}
if [[ ! ($(which docker) && $(docker --version)) ]]; then
    install_docker
else
    tips "docker已经安装过!";
    oldVersion=$(docker -v|sed -r 's/.*version//'|sed -r 's/,.*//'|sed 's/ //g')
    tips "docker安装版本是：$oldVersion";
    needVersion="19.03.12"
    if [[ $oldVersion != $needVersion ]]; then
        tips "docker安装版本不符合，需要更新";
        install_docker
    fi
fi
if [[ ! ($(which docker) && $(docker --version)) ]]; then
    	error_tips "docker 安装失败，可能是网络异常，请重试"
	else
		tips "docker 安装成功";
fi
# 安装docker-compose
function install_compose() {
    tips "安装docker-compose"
    curl -L https://get.daocloud.io/docker/compose/releases/download/1.27.0-rc3/docker-compose-`uname -s`-`uname -m` > /usr/local/bin/docker-compose
    chmod +x /usr/local/bin/docker-compose
    [ -f /usr/bin/docker-compose ] || ln -s /usr/local/bin/docker-compose /usr/bin/docker-compose
    docker-compose version
}
if [[  ! ($(which docker-compose) && $(docker-compose version)) ]]; then
    tips "docker-compose还没有安装过";
    install_compose
else
    tips "docker-compose已经安装过!";
    oldVersion=$(docker-compose -v|sed -r 's/.*version//'|sed -r 's/,.*//'|sed 's/ //g')
    tips "docker-compose安装版本是：$oldVersion";
    needVersion="1.27.0-rc3"
    if [[ $oldVersion != $needVersion ]]; then
        tips "docker-compose安装版本不符合，需要更新";
        install_compose
    fi
fi
if [[  ! ($(which docker-compose) && $(docker-compose version)) ]]; then
    	error_tips "docker-compose 安装失败"
	else
		tips "docker-compose 安装成功";
fi
docker ps -a
if [ $? -ne 0 ]; then
    tips "启动docker服务";
	systemctl enable docker
	sudo systemctl restart docker
fi

if [ ! -d libssh2-1.9.0 ];then
    tips "安装libssh2";
	tar zxf libssh2-1.9.0.tar.gz
	cd libssh2-1.9.0
	./configure && make && make install
	cd ..
fi
tips "初始化代码目录和权限";
if [ ! -d "server/panel/mysql-data" ];then
	mkdir -p server/panel/mysql-data
fi
chmod +x /bctos/server/panel/entrypoint.sh
chmod -R 777 /bctos/wwwroot/bctos.cn/public/kod/
cd wwwroot/bctos.cn
if [ ! -d runtime ];then
	mkdir runtime
fi
if [ ! -d "db/migrations" ];then
	mkdir -p db/migrations
fi
if [ ! -d "public/storage" ];then
	mkdir -p public/storage
fi
#在容器中82表示www-data用户
if [ -z $(cat /etc/passwd|grep www-data) ];then
    tips "增加www-data用户";
    groupadd -g 82 www-data
    useradd -u 82 -g 82  www-data
fi

chown -R 82.82 ./*
chmod -R +x scripts
chmod -R 755 public runtime db app

tips "替换配置文件中的密码";
Get_Ip_Address
sed -i "s/123456/${MYSQL_PWD}/" config/database.php
sed -i "s/192\.168\.0\.8/${LOCAL_IP}/" config/weiphp_define.php
sed -i "/SSH_PAWD/{s/123/${SSH_PAWD}/}" config/weiphp_define.php
cd ../..

tips "代码准备完毕，目录如下：";
ls -l

tips "下载面板镜像，使用docker-compose启动面板容器";
docker-compose up -d
if [ $? -ne 0 ]; then
    error_tips "镜像下载成功失败，请先手工下载试试：docker pull wxm201411/panel"
else
    tips "服务启动成功";
fi


tips "==================================================================
恭喜! 小韦云面板安装成功了!
==================================================================
外网面板地址: http://${getIpAddress}:666
内网面板地址: http://${LOCAL_IP}:666
用户名: admin
密码: 123456

代码的目录在：/bctos/wwwroot
账号信息保存在：/bctos/account.log
如果你无法访问小韦云面板，请检查防火墙/安全组是否有放行面板[666]端口
==================================================================
" > /bctos/account.log
cat /bctos/account.log