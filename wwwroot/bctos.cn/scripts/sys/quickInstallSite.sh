#!/bin/bash
mysql=${1:-"not"}
redis=${2:-"not"}
memcached=${3:-"not"}
php=${4:-"not"}
php_func=${5:-'-'}
php_ext=${6:-'-'}
domain=$7
download_type=$8
download_url=$9
db_file=${10:-'-'}
redis_file=${11:-'-'}
memcached_file=${12:-'-'}
rm_file=${13:-'-'}
db_set=${14:-'utf8mb4'}
db_pwd=${15:-'123456'}

# 需要用的软件
function installSoft(){
    if [[  ! $(which $1) ]]; then
        echo "============Install $1 begin================================="
        yum -y install $1
        echo "============Install $1 end, return status: $?==================="
    fi
}
start_time=$(date +"%s.%N")
diff_time=0
function timediff() {
    end_time=$(date +"%s.%N")

    start_s=${start_time%.*}
    start_nanos=${start_time#*.}
    end_s=${end_time%.*}
    end_nanos=${end_time#*.}

    if [ "$end_nanos" -lt "$start_nanos" ];then
        end_s=$(( 10#$end_s - 1 ))
        end_nanos=$(( 10#$end_nanos + 10**9 ))
    fi

    diff_time=$(( 10#$end_s - 10#$start_s )).`printf "%03d\n" $(( (10#$end_nanos - 10#$start_nanos)/10**6 ))`
    start_time=${end_time}
}
error_tips(){
    timediff
	echo -e "==============\033[32m 耗时：${diff_time} 秒 \033[0m==============";
	echo -e  "\033[31m $1  \033[0m";
	echo '==over==error'
	exit 1
}
tips(){
    timediff
	echo -e "==============\033[32m 耗时：${diff_time} 秒 \033[0m==============";
    echo -e "\033[32m $1 \033[0m"
}

installSoft wget
installSoft git

tips "下载源码"
cd /bctos/wwwroot

[ -d ${domain} ] && rm -rf /bctos/wwwroot/${domain}
if [[ ${download_type} == "git" ]];then
    git clone ${download_url} ${domain}
fi
if [[ ${download_type} == "composer" ]];then
    docker exec panel sh -c "cd /bctos/wwwroot;composer create-project ${download_url} ${domain}"
fi

[ ! -d ${domain} ] && mkdir ${domain}
cd ${domain}
if [[ ${download_type} == "wget" ]];then
    wget -O install.zip ${download_url}

    installSoft unzip
    unzip -q install.zip
    rm -f install.zip

    #判断如果只有一个目录，需要取消这个目录
    if [[ $(ls|wc -l) == 1 ]];then
        dir=$(ls|head -1)
        mv $dir/* ./
        rm -rf $dir
    fi
fi

if [[ $(ls -A) == '' ]];then
    error_tips "源码下载失败，请稍等再试"
else
    tips "源码下载成功"
fi

ls -l

if [ -f composer.json ] && [ ! -f composer.lock ]; then
    tips "发现composer.json，执行composer install"
    docker exec panel sh -c "cd /bctos/wwwroot/${domain};composer install"
fi

tips "先检查所需要的容器服务是否存在，不存在的话先更新面板"
cd /bctos/server
server_check=1
[[ ${server_check} -eq 1 ]] && [[ $mysql != 'not' ]] && [ -z $(find . -type d -name $mysql) ] && server_check=0
[[ ${server_check} -eq 1 ]] && [[ $redis != 'not' ]] && [ -z $(find . -type d -name $redis) ] && server_check=0
[[ ${server_check} -eq 1 ]] && [[ $memcached != 'not' ]] && [ -z $(find . -type d -name $memcached) ] && server_check=0
[[ ${server_check} -eq 1 ]] && [[ $php != 'not' ]] && [ -z $(find . -type d -name $php) ] && server_check=0


if [[ ${server_check} -eq 0 ]];then
    tips "有不存在的容器，先更新面板"
    /bctos/wwwroot/bctos.cn/scripts/sys/updatePanel.sh
else
    tips "检查完毕，不需要更新"
fi


if [[ $php != 'not' ]];then
    tips "设置PHP禁用函数，设置PHP扩展，重新运行PHP容器"
    cd /bctos/server/$php

    if [[ $php_func != '-' ]];then
        tips "解禁PHP函数：$php_func"
        #给函数前后都加逗号，这样可以加上逗号一起替换，防止有些函数名存在歧义，如exec和pcntl_exec，如果要开启exec，前后没有逗号的话也能匹配到pcntl_exec
        sed -i -r -e '/^[ ]*disable_functions/{s/$/,/;s/=[ ]*/=,/}' php.ini
        array=(${php_func//,/ })
        for var in ${array[@]}
        do
            sed -i -r  "/^[ ]*disable_functions/{s/,${var},/,/}" php.ini
        done
        #去掉函数前后都加逗号
        sed -i -r -e '/^[ ]*disable_functions/{s/,$//;s/=[ ]*,/= /}' php.ini
    fi
    if [[ ${php_ext} != '-' ]];then
        tips "设置PHP扩展：${php_ext}"
        image=$(sed -n -r '/^[ ]+image:[ ]+/p' docker-compose.yml |sed 's/image://'|sed 's/ //g')
        array2=(${php_ext//,/ })

        ext_install=0
        tips "更新Dockerfile里的install-php-extensions扩展信息"
        dockerfile=$(cat Dockerfile)
         for ext in ${array2[@]}
            do
              if [ -z "$(echo $dockerfile |grep ' '$ext' ')" ];then
                 tips "不存在扩展，需要安装"
                 ext_install=1
                 sed -i "s/ install-php-extensions/ install-php-extensions $ext /" Dockerfile;
              fi
         done

         if [[ ${ext_install} -eq 1 ]];then
            if [ ! -z $(docker ps --format '{{.Names}}'|grep $php) ];then
                tips "$php 容器已经在运行，通过php -m判断是否已安装"
                for ext in ${array2[@]}
                do
                  if [ -z $(docker exec $php php -m |grep -w $ext) ];then
                    tips "在容器中执行安装扩展：$ext"
                     docker exec $php install-php-extensions $ext
                  fi
                done
                tips "更新镜像：$image"
                docker commit $php $image;
                docker-compose down
                docker rmi $(docker images -f 'dangling=true' -q);
                docker-compose up -d;
            else
                tips "$php 容器没有在运行，判断是否有镜像，如果有则删除镜像,最后启动PHP容器"
                docker-compose down
                if [ -z $(docker images --format '{{.Repository}}|||{{.Tag}}'|grep $image) ];then
                    tips "删除镜像：$image"
                    docker rmi $image
                fi
                tips "启动PHP容器"
                docker-compose up -d
            fi
        else
            tips "以上的PHP扩展都已经存在，不需要安装"
        fi
    fi
fi

tips "伪静态文件生成，网站nginx文件生成"
cd /bctos/server/nginx
if [ ! -d rewrite ];then
    mkdir rewrite
    chmod 777 rewrite
fi
if [ -f /bctos/wwwroot/bctos.cn/runtime/${domain}".rewrite.conf" ];then
    mv /bctos/wwwroot/bctos.cn/runtime/${domain}".rewrite.conf" ./rewrite/${domain}".rewrite.conf"
else
    echo '' > ./rewrite/${domain}".rewrite.conf"
fi
mv /bctos/wwwroot/bctos.cn/runtime/${domain}".conf" ./conf.d/${domain}".conf"
chmod -R 777 rewrite
chmod -R 777 conf.d

tips "启动所需的容器"
function setUpDocker(){
    if [[ $1 != 'not' ]] && [ -z $(docker ps --format '{{.Names}}'|grep $1) ];then
        tips "启动容器：$1"
         cd /bctos/server/$1
         docker-compose up -d
         need_start=1
     fi
}
setUpDocker $mysql
setUpDocker $redis
setUpDocker $memcached
setUpDocker $php
sleep 2;


cd /bctos/wwwroot
chown -R 82.82 ${domain}
chmod -R 755 ${domain}
cd ${domain}
tips "增加防跨站攻击配置"
echo "open_basedir=/bctos/wwwroot/${domain}/:/tmp/" > ./.user.ini
if [[ ${domain} == 'default' ]];then
    db_name='bctos_default'
else
    db_name=$(echo ${domain}|sed 's/\./_/g')
fi

if [[ $mysql != 'not' ]];then
    tips "创建数据库和账号"
    root_pwd=$(grep 'MYSQL_ROOT_PASSWORD' /bctos/server/${mysql}/docker-compose.yml | sed -r 's/MYSQL_ROOT_PASSWORD://' | sed 's/\\r//;s/\\n//;s/ //g')
#    echo $root_pwd
    if [[ $mysql == 'mysql56' ]];then
        docker exec -e MYSQL_PWD=$root_pwd -i ${mysql} mysql -uroot << EOF
CREATE DATABASE ${db_name} DEFAULT CHARACTER SET ${db_set};
CREATE USER '${db_name}'@'%' IDENTIFIED WITH mysql_native_password BY '${db_pwd}';
GRANT ALL ON ${db_name}.* TO '${db_name}'@'%';
flush privileges;
EOF
    else
        docker exec -e MYSQL_PWD=$root_pwd -i ${mysql} mysql -uroot << EOF
CREATE DATABASE IF NOT EXISTS ${db_name} DEFAULT CHARACTER SET ${db_set};
CREATE USER IF NOT EXISTS '${db_name}'@'%' IDENTIFIED WITH mysql_native_password BY '${db_pwd}';
GRANT ALL ON ${db_name}.* TO '${db_name}'@'%';
flush privileges;
EOF
    fi

fi

if [[ $mysql != 'not' ]] && [ -f install.sql ];then
    tips "发现install.sql，执行数据库导入,时间可能较长，请耐心等待"
docker exec -e MYSQL_PWD=$root_pwd -i ${mysql} mysql -uroot << EOF
set global innodb_flush_log_at_trx_commit = 2;
set global sync_binlog = 2000;
EOF
    docker exec -i  ${mysql} sh -c "exec mysql -uroot -p${root_pwd} ${db_name}" < ./install.sql
docker exec -e MYSQL_PWD=$root_pwd -i ${mysql} mysql -uroot << EOF
set global innodb_flush_log_at_trx_commit = 1;
set global sync_binlog = 1;
EOF
    tips "导入数据库完成"
fi

if [[ $mysql != 'not' ]] && [[ ${db_file} != '-' ]];then
    tips "替换数据库配置文件的连接信息"
    sed -i -e "s/BCTOS_DB_HOST/$mysql/g" -e "s/BCTOS_DB_NAME/$db_name/g" -e "s/BCTOS_DB_USER/${db_name}/g" -e "s/BCTOS_DB_PWD/${db_pwd}/g" ${db_file}
fi
if [[ $redis != 'not' ]] && [[ ${redis_file} != '-' ]];then
    tips "替换redis配置文件的连接信息"
    sed -i -e "s/BCTOS_REDIS_HOST/$redis/g" -e "s/BCTOS_REDIS_PORT/6379/g" -e "s/BCTOS_REDIS_PWD//g" ${redis_file}
fi
if [[ $memcached != 'not' ]] && [[ ${memcached_file} != '-' ]];then
    tips "替换memcached配置文件的连接信息"
    sed -i -e "s/BCTOS_MEMCACHED_HOST/$memcached/g" -e "s/BCTOS_MEMCACHED_PORT/11211/g" -e "s/BCTOS_MEMCACHED_PWD//g" ${memcached_file}
fi
if [ -f install.sh ];then
    tips "发现install.sh文件，执行它"
    chmod +x install.sh
    ./install.sh ${domain} $mysql $db_name
fi

if [[ ${rm_file} != '-' ]];then
    tips "清空安装文件"
    array=(${rm_file//#@#/ })
    for var in ${array[@]}
    do
        tips "删除：${var}"
       rm -rf $var
    done
fi

tips "nginx重载配置"
cd /bctos/server/nginx
[ -z $(docker ps --format '{{.Names}}'|grep nginx) ] && docker-compose up -d || docker restart nginx

tips "安装完成，网站已经成功安装好"

#通知前端已经执行成功执行完毕
echo '==over==success'
