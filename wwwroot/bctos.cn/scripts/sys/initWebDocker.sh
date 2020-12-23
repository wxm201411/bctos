#!/bin/bash
cd /bctos/server
names=$(docker ps -a --format '{{.Names}}')
[ -z $(echo $names|grep 'php'|sed 's/ //g') ] && docker-compose -f php74/docker-compose.yml up -d
[ -z $(echo $names|grep 'mysql'|sed 's/ //g') ] && docker-compose -f mysql57/docker-compose.yml up -d
[ -z $(echo $names|grep 'nginx'|sed 's/ //g') ] && docker-compose -f nginx/docker-compose.yml up -d



