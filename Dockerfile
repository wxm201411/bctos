FROM alpine
LABEL maintainer="bctos.cn"

ENV MYSQL_ROOT_PASSWORD=bctos_panel
ENV MYSQL_DATABASE=bctos_panel
ENV MYSQL_CHARSET=utf8mb4
ENV MYSQL_COLLATION=utf8mb4_general_ci

COPY server/panel/entrypoint.sh /bctos/server/panel/entrypoint.sh
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN chmod 777 /bctos/server/panel/entrypoint.sh \
&& addgroup -g 82 -S www-data; 	adduser -u 82 -D -S -G www-data www-data \
&& sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g' /etc/apk/repositories \
&& apk update \
&& apk add --no-cache curl tzdata \
&& cp -rf /usr/share/zoneinfo/Asia/Shanghai /etc/localtime \
&& apk add --no-cache nginx php7 php7-fpm php7-ctype php7-curl php7-dom php7-fileinfo \
php7-gd php7-iconv php7-json php7-mbstring php7-mysqlnd php7-pdo php7-pcntl php7-openssl \
php7-opcache php7-pdo_mysql  php7-posix php7-session php7-pdo_sqlite php7-simplexml php7-memcached \
php7-xml php7-xmlreader php7-xmlwriter php7-simplexml php7-pecl-ssh2 php7-pecl-yaml php7-phar \
mariadb mariadb-client mariadb-server-utils \
&& apk del tzdata \
&& rm -rf /var/cache/apk/*

EXPOSE 80 443 3306 2100 2120 2121

ENTRYPOINT ["/bctos/server/panel/entrypoint.sh"]
