#!/bin/bash
doamin=$1
key=$2
pem=$3
#多nginx版本支持
docker=${4:-"nginx"}

domain_path=/bctos/server/${docker}/conf.d/cert/$doamin
if [ ! -d $domain_path ]; then
    mkdir -p $domain_path
fi
# 把证书文件从上传目录复制到指定的证书目录中
\cp -f $key $domain_path/ssl.key
\cp -f $pem $domain_path/ssl.pem

# 写入到nginx配置文件
conf=$domain".conf"

check=$(egrep "listen[ \t]+443" /bctos/server/${docker}/conf.d/$conf)
if [[ ! $check ]]; then
    #端口不存在，先增加443端口
	sed -i -r '/server_name[ \t]+/i\    listen 443 ssl http2;' /bctos/server/${docker}/conf.d/$conf
fi

check=$(grep "$domain_path/ssl.key" /bctos/server/${docker}/conf.d/$conf)
if [[ ! $check ]]; then
    #证书配置不存在，先增加证书配置
	sed -i -r '/[ \t]+index[ \t]+/a\ \
    ssl_certificate    '$domain_path'/ssl.pem; \
    ssl_certificate_key    '$domain_path'/ssl.key; \
    ssl_protocols TLSv1.1 TLSv1.2 TLSv1.3; \
    ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:HIGH:!aNULL:!MD5:!RC4:!DHE; \
    ssl_prefer_server_ciphers on; \
    ssl_session_cache shared:SSL:10m; \
    ssl_session_timeout 10m;    \
    ' /bctos/server/${docker}/conf.d/$conf
fi

#重启nginx
docker restart ${docker} > /dev/null

#输出证书过期时间
if [[  !($(which openssl) && $(openssl version)) ]]; then
    yum -y install openssl && yum -y install openssl-devel > /dev/null
fi

openssl x509 -in $domain_path/ssl.pem -noout -dates