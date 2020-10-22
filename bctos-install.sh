#!/bin/bash
LANG=en_US.UTF-8

MYSQL_PWD=${1:-'3306'}
if [ !$MYSQL_PWD ];then
	MYSQL_PWD=$(head /dev/urandom |cksum |md5sum |cut -c 1-9)
fi
NGINX_PORT=${2:-'80'}
MYSQL_PORT=${3:-'3306'}
PHP_PORT=${4:-'9000'}

if [ ! -d "/data" ]; then
	mkdir /data
fi	
cd /data

echo "
+----------------------------------------------------------------------
| Bctos FOR CentOS/Ubuntu/Debian
+----------------------------------------------------------------------
| Copyright © 2015-2099 小韦云科技(https://www.bctos.cn) All rights reserved.
+----------------------------------------------------------------------
| The WebPanel URL will be http://SERVER_IP:666 when installed.
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

Red_Error(){
	echo '=================================================';
	printf '\033[1;31;40m%b\033[0m\n' "$1";
	GetSysInfo
	exit 1;
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
	Red_Error "抱歉, 系统不支持32位系统, 请使用64位系统或安装小韦云链!";
fi


yum -y install which

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
if [ -d bctos ];then
	cd bctos
	git pull
	cd ..
else
	git clone https://gitee.com/bctos_cn/bctos.git
	if [ $? -ne 0 ]; then
		git clone https://github.com/wxm201411/bctos.git
	fi
fi
if [[ ! -d "bctos/www" ]]; then
	    Red_Error "git clone fail"
	else
		echo "git clone success";
fi
cd bctos
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
    	Red_Error "docker 安装失败"
	else
		echo "docker install success";
fi
# 安装docker-compose
if [[  ! ($(which docker-compose) && $(docker-compose --version)) ]]; then
    echo "============Install docker-compose==================="
    #curl -L "https://github.com/docker/compose/releases/download/1.24.1/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    curl -L https://get.daocloud.io/docker/compose/releases/download/1.24.1/docker-compose-`uname -s`-`uname -m` > /usr/local/bin/docker-compose
    chmod +x /usr/local/bin/docker-compose
    ln -s /usr/local/bin/docker-compose /usr/bin/docker-compose
    docker-compose --version
fi
if [[  ! ($(which docker-compose) && $(docker-compose --version)) ]]; then
    	Red_Error "docker-compose 安装失败"
	else
		echo "docker-compose install success";
fi
docker ps -a
if [ $? -ne 0 ]; then
	systemctl enable docker
	systemctl start docker
fi


if [ ! -d "mysql-data" ];then
	mkdir mysql-data
fi
cd www
if [ ! -d runtime ];then
	mkdir runtime
fi
if [ ! -d "db/migrations" ];then
	mkdir -p db/migrations
fi
if [ ! -d "public/storage" ];then
	mkdir -p public/storage
fi

chmod 777 start.sh
chmod -R +x scripts
chmod -R 755 public runtime db app
sed -i "s/123456/${MYSQL_PWD}/" config/database.php
cd ..
cp -f docker-compose.yml.bar docker-compose.yml

sed -i "s/80\:/${NGINX_PORT}\:/" docker-compose.yml
sed -i "s/3306\:/${MYSQL_PORT}\:/" docker-compose.yml
sed -i "s/9000\:/${PHP_PORT}\:/" docker-compose.yml
sed -i "s/bctosMysqlPwd/${MYSQL_PWD}/" docker-compose.yml
docker-compose -f docker-compose.yml up -d
echo "==========docker-compose up success=================";

docker exec -it php /bin/bash -c "/var/www/html/start.sh"

Get_Ip_Address
result="================================================================== \
\033[32mCongratulations! Installed successfully!\033[0m \
================================================================== \
echo  \"外网面板地址: http://${getIpAddress}:666 \
echo  \"内网面板地址: http://${LOCAL_IP}:666 \
username: admin \
password: 123 \
mysql连接信息: \
${getIpAddress}:3309 \
username: root \
password: ${MYSQL_PWD} \
代码在本机目录：/data/bctos-install/www \
代码在容器目录：/var/www/html \
账号信息保存在：$(pwd)/account.log
\033[33mIf you cannot access the panel,\033[0m \
\033[33mrelease the following panel port [666] in the security group\033[0m \
\033[33m若无法访问面板，请检查防火墙/安全组是否有放行面板[666]端口\033[0m \
=================================================================="
echo -e result > account.log
echo -e result