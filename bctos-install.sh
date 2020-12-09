#!/bin/bash
LANG=en_US.UTF-8

#MYSQL_PWD=${1:-''}
#if [ ! $MYSQL_PWD ];then
#	MYSQL_PWD=$(head /dev/urandom |cksum |md5sum |cut -c 1-9)
#fi

echo "
+----------------------------------------------------------------------
| 小韦云面板支持 CentOS/Ubuntu/Debian 系统
+----------------------------------------------------------------------
| Copyright © 2020-2099 小韦云科技(https://www.bctos.cn) All rights reserved.
+----------------------------------------------------------------------
| 小韦云面板安装成功后可通过 http://你的服务器IP:666 进行访问.
+----------------------------------------------------------------------
"
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

error_tip(){
	echo '=================================================';
	printf '\033[1;31;40m%b\033[0m\n' "$1";
	GetSysInfo
	exit 1;
}
tips(){
	echo '=================================================';
    echo -e "\033[32m $1 \033[0m"
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
	error_tip "抱歉, 系统不支持32位系统, 请使用64位系统或安装小韦云链!";
fi
#check=$(lsof -i :$NGINX_PORT | awk 'END {print $1}')
#if [ $check ]; then
#	error_tip "nginx的端口号： $NGINX_PORT 已被占用，占用的服务是：$check，请关闭此服务或换端口再试";
#fi
#check=$(lsof -i :$MYSQL_PORT | awk 'END {print $1}')
#if [ $check ]; then
#	error_tip "mysql的端口号： $MYSQL_PORT 已被占用，占用的服务是：$check，请关闭此服务或换端口再试";
#fi
#check=$(lsof -i :$PHP_PORT | awk 'END {print $1}')
#if [ $check ]; then
#	error_tip "php的端口号： $(PHP_PORT) 已被占用，占用的服务是：$(check)，请关闭此服务或换端口再试";
#fi

if [ ! -d "/bctos" ]; then
	mkdir /bctos
fi	
cd /bctos

which -v
if [ $? -ne 0 ]; then
	yum -y install which
fi

if [[  !($(which wget) && $(wget --version)) ]]; then
    echo "============Install wget==================="
    yum -y install wget
fi
if [[  !($(which curl) && $(curl --version)) ]]; then
    echo "============Install curl==================="
    yum -y install curl
fi
if [[  !($(which git) && $(git --version)) ]]; then
    echo "============Install git==================="
    yum -y install git
fi
if [[  $(which podman) ]]; then
    echo "============remove podman==================="
    yum -y remove podman
fi
echo "============download file from gitee ==================="
if [ -d conf.d ];then
	git pull
else
	git clone https://gitee.com/bctos_cn/bctos.git ./
	if [ $? -ne 0 ]; then
		git clone https://github.com/wxm201411/bctos.git ./
	fi
fi
if [[ ! -d "wwwroot" ]]; then
	    error_tip "git clone fail"
	else
		echo "git clone success";
fi
# 安装docker
if [[ ! ($(which docker) && $(docker --version)) ]]; then
	echo "============Install docker==================="
	yum install -y containerd.io-1.2.6-3.3.fc30.x86_64.rpm
	yum remove -y docker docker-client docker-client-latest docker-common docker-latest docker-latest-logrotate docker-logrotate docker-engine
    #curl -fsSL https://get.docker.com | bash -s docker --mirror Aliyun
    #curl -sSL https://get.daocloud.io/docker | sh
    yum install -y yum-utils device-mapper-persistent-data lvm2
    yum-config-manager --add-repo http://mirrors.aliyun.com/docker-ce/linux/centos/docker-ce.repo
    yum install -y docker-ce docker-ce-cli containerd.io

	mkdir -p /etc/docker
	tee /etc/docker/daemon.json <<-'EOF'
{
  "registry-mirrors": ["https://u1fynok0.mirror.aliyuncs.com"]
}
EOF

    systemctl enable docker
	systemctl start docker
fi
if [[ ! ($(which docker) && $(docker --version)) ]]; then
    	error_tip "docker 安装失败"
	else
		echo "docker install success";
fi
# 安装docker-compose
function version_lt() { test "$(echo "$@" | tr " " "\n" | sort -rV | head -n 1)" != "$1"; }
function install_compose() {
    echo "============Install docker-compose==================="
    curl -L https://get.daocloud.io/docker/compose/releases/download/1.27.4/docker-compose-`uname -s`-`uname -m` > /usr/local/bin/docker-compose
    chmod +x /usr/local/bin/docker-compose
    ln -s /usr/local/bin/docker-compose /usr/bin/docker-compose
    docker-compose version
}
if [[  ! ($(which docker-compose) && $(docker-compose version)) ]]; then
    install_compose
else
    oldVersion=$(docker-compose version|grep docker-py|sed 's/docker-py version: //'|sed 's/ //g')
    needVersion="1.27.4"
    if version_lt $oldVersion $needVersion; then
        install_compose
    fi
fi
if [[  ! ($(which docker-compose) && $(docker-compose version)) ]]; then
    	error_tip "docker-compose 安装失败"
	else
		echo "docker-compose install success";
fi
docker ps -a
if [ $? -ne 0 ]; then
	systemctl enable docker
	systemctl start docker
fi

if [ ! -d libssh2-1.9.0 ];then
	tar zxf libssh2-1.9.0.tar.gz
	cd libssh2-1.9.0
	./configure && make && make install
	cd ..
fi

if [ ! -d "server/panel/mysql-data" ];then
	mkdir -p server/panel/mysql-data
fi
chmod +x /bctos/server/panel/entrypoint.sh
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
    groupadd -g 82 www-data
    useradd -u 82 -g 82  www-data
fi

chown -R 82.82 ./*
chmod -R +x scripts
chmod -R 755 public runtime db app

sed -i "s/123456/${MYSQL_PWD}/" config/database.php
cd ../..
echo -e $(ls)

docker-compose up -d
echo "==========docker-compose up success=================";
Get_Ip_Address
echo "==================================================================
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