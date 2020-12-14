#!/bin/bash
LANG=en_US.UTF-8


soft_id=$1

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


tips "您的软件准备开始安装"

if [ ! -d "/bctos/wwwroot" ];then
    tips "还没安装小韦云面板，先安装它"
    wget -O install.sh https://www.bctos.cn/install/bctos-install.sh && chmod +x install.sh && ./install.sh
else
    tips  "已经安装了小韦云面板"
fi

domain=''
function domain_input(){
    tips "请输入网站,为空时表示使用IP访问"
    read -s -p "访问域名：" domain
    echo -e ""
    domain=$(echo $domain|sed 's/https:\/\///'|sed 's/http:\/\///'|sed 's/^\///'|sed 's/\/$//'|sed 's/ //g')
    if [[ $domain != '' && -d /bctos/wwwroot/$domain ]];then
        tips "该域名的网站已经存在，请换名再试"
        domain_input
    fi
}
tips "判断默认网站是否已经安装"
default_path=$(cat /bctos/server/nginx/conf.d/localhost.conf |egrep ' root '| sed 's/root//g'|sed 's/ //g')
if [[ $default_path == "/bctos/wwwroot/localhost;" ]];then
    tips "未安装，直接把当前网站设置为默认网站"
else
    tips "已安装，需要用户输入域名"
    domain_input
fi
if [[ $domain == '' ]];then
    domain='localhost'
fi

tips "从官网下载配置的参数"
php=null
try_count=0
function download_conf(){
    eval `curl -sS --connect-timeout 10 -m 60 https://www.bctos.cn/index.php?s=/home/index/param/id/${soft_id}`
    if [[ $php == "null" && $try_count != 3 ]];then
        tips "下载配置的失败，重试中"
        sleep 1
        let try_count+=1
        download_conf
    fi
}
if [[ $php == "null" ]];then
    error_tips "下载配置的失败，可能官网系统繁忙，请稍等再试"
else
    tips "配置下载成功"
fi



tips "先检查所需要的容器服务是否存在，不存在的话先更新面板"
cd /bctos/server
server_check=1
[[ server_check -eq 1 ]] && [[ $mysql != 'not' ]] && [ -z $(find . -type d -name $mysql) ] && server_check=0
[[ server_check -eq 1 ]] && [[ $redis != 'not' ]] && [ -z $(find . -type d -name $redis) ] && server_check=0
[[ server_check -eq 1 ]] && [[ $memcached != 'not' ]] && [ -z $(find . -type d -name $memcached) ] && server_check=0
[[ server_check -eq 1 ]] && [[ $php != 'not' ]] && [ -z $(find . -type d -name $php) ] && server_check=0


if [[ server_check -eq 0 ]];then
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
    if [[ $php_ext != '-' ]];then
        tips "设置PHP扩展：$php_ext"
        image=$(sed -n -r '/^[ ]+image:[ ]+/p' docker-compose.yml |sed 's/image://'|sed 's/ //g')
        array2=(${php_ext//,/ })

        ext_install=0
        tips "更新Dockerfile里的install-php-extensions扩展信息"
        dockerfile=$(cat Dockerfile)
         for ext in ${array2[@]}
            do
              if [ -z $(echo $dockerfile |grep $ext) ];then
                 tips "不存在扩展，需要安装"
                 ext_install=1
                 sed -i "s/ install-php-extensions/ install-php-extensions $ext /" Dockerfile;
              fi
         done
         if [[ $ext_install -eq 1 ]];then
            if [ ! -z $(docker ps --format '{{.Names}}'|grep $php) ];then
                tips "$php 容器已经在运行，通过php -m判断是否已安装"
                for ext in ${array2[@]}
                do
                  if [ -z $(docker exec $php php -m |grep $ext) ];then
                    tips "在容器中执行安装扩展：$ext"
                     docker exec $php install-php-extensions $ext
                  fi
                done
                tips "更新镜像：$image"
                docker commit $php $image;
                docker rmi $(docker images -f 'dangling=true' -q);
                docker restart $php;
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
if [ -f /bctos/wwwroot/bctos.cn/runtime/$domain".rewrite.conf" ];then
    mv /bctos/wwwroot/bctos.cn/runtime/$domain".rewrite.conf" ./rewrite/$domain".rewrite.conf"
else
    curl -sS --connect-timeout 10 -m 60 https://www.bctos.cn/index.php?s=/home/index/rewrite/id/${soft_id} > ./rewrite/$domain".rewrite.conf"
fi
if [ -f /bctos/wwwroot/bctos.cn/runtime/$domain".conf" ];then
    mv /bctos/wwwroot/bctos.cn/runtime/$domain".conf" ./conf.d/$domain".conf"
else
    curl -sS --connect-timeout 10 -m 60 https://www.bctos.cn/index.php?s=/home/index/conf/id/${soft_id}/domain/${domain} > ./conf.d/$domain".conf"
fi

chmod -R 777 rewrite
chmod -R 777 conf.d

tips "启动所需的容器"

[[ $mysql != 'not' ]] && [ -z $(docker ps --format '{{.Names}}'|grep $mysql) ] && $(cd /bctos/server/$mysql; docker-compose up -d; need_start=1)
[[ $redis != 'not' ]] && [ -z $(docker ps --format '{{.Names}}'|grep $redis) ] && $(cd /bctos/server/$redis; docker-compose up -d; need_start=1)
[[ $memcached != 'not' ]] && [ -z $(docker ps --format '{{.Names}}'|grep $memcached) ] && $(cd /bctos/server/$memcached; docker-compose up -d; need_start=1)
[[ $php != 'not' ]] && [ -z $(docker ps --format '{{.Names}}'|grep $php) ] && $(cd /bctos/server/$php; docker-compose up -d; need_start=1)
sleep 2;
tips "增加网站 下载源码"
cd /bctos/wwwroot

[ -d $domain ] && rm -rf /bctos/wwwroot/$domain
if [[ $download_type == "git" ]];then
    git clone $download_url $domain
fi
if [[ $download_type == "composer" ]];then
    docker exec panel sh -c "cd /bctos/wwwroot;composer create-project $download_url $domain"
fi

[ ! -d $domain ] && mkdir $domain
cd $domain
if [[ $download_type == "wget" ]];then
    wget -O install.zip $download_url

    installSoft unzip
    unzip install.zip
    rm -f install.zip
fi

if [[ $(ls -A) == '' ]];then
    error_tips "源码下载失败，请稍等再试"
else
    tips "源码下载成功"
fi

ls -l
if [ -f composer.json ] && [ ! -f composer.lock ]; then
    tips "发现composer.json，执行composer install"
    docker exec panel sh -c "/bctos/wwwroot/${domain};composer install"
fi
cd ..
chown -R 82.82 $domain
chmod -R 755 $domain
cd $domain
tips "增加防跨站攻击配置"
echo "open_basedir=/bctos/wwwroot/$domain/:/tmp/" > ./.user.ini

db_name=$(echo $domain|sed 's/\./_/g')
if [[ $mysql != 'not' ]];then
    tips "创建数据库和账号"
    root_pwd=$(grep 'MYSQL_ROOT_PASSWORD' /bctos/server/${mysql}/docker-compose.yml | sed -r 's/MYSQL_ROOT_PASSWORD://' | sed 's/ //g')
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
    tips "发现install.sql，执行数据库导入"
    docker exec -i  ${mysql} sh -c "exec mysql -uroot -p${root_pwd} ${db_name}" < ./install.sql
fi

if [[ $mysql != 'not' ]] && [[ $db_file != '-' ]];then
    tips "替换数据库配置文件的连接信息"
    sed -i -e "s/BCTOS_DB_HOST/$mysql/g" -e "s/BCTOS_DB_NAME/$db_name/g" -e "s/BCTOS_DB_USER/${db_name}/g" -e "s/BCTOS_DB_PWD/${db_pwd}/g" $db_file
fi
if [[ $redis != 'not' ]] && [[ $redis_file != '-' ]];then
    tips "替换redis配置文件的连接信息"
    sed -i -e "s/BCTOS_REDIS_HOST/$redis/g" -e "s/BCTOS_REDIS_PORT/6379/g" -e "s/BCTOS_REDIS_PWD//g" $redis_file
fi
if [[ $memcached != 'not' ]] && [[ $memcached_file != '-' ]];then
    tips "替换memcached配置文件的连接信息"
    sed -i -e "s/BCTOS_MEMCACHED_HOST/$memcached/g" -e "s/BCTOS_MEMCACHED_PORT/11211/g" -e "s/BCTOS_MEMCACHED_PWD//g" $memcached_file
fi
if [ -f install.sh ];then
    tips "发现install.sh文件，执行它"
    chmod +x install.sh
    ./install.sh $domain
fi

tips "数据库增加记录"
docker exec panel sh -c "cd /bctos/wwwroot/bctos.cn;php think sample_install ${soft_id} ${domain} ${db_name}"


if [[ $rm_file != '-' ]];then
    tips "清空安装文件"
    rm -rf $rm_file
fi

tips "nginx重载配置"
cd /bctos/server/nginx
[ -z $(docker ps --format '{{.Names}}'|grep nginx) ] && docker-compose up -d || docker restart nginx

tips "安装完成，网站已经成功安装好"

#通知前端已经执行成功执行完毕
echo '==over==success'