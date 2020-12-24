#!/bin/bash
start=$(date +"%s.%N")
startForam=$(date "+%Y-%m-%d %H:%M:%S")

db_name=$1
file=$(date "+%Y%m%d%H%M%S")"_"$2
logNum=$3
#多数据库支持
docker=${4:-"mysql57"}
id=$5


backup=/bctos/wwwroot/bctos.cn/public/storage/backup/$id

if [[  !($(which zip) && $(zip -v)) ]]; then
    yum -y install unzip zip > /dev/null
fi

if [ ! -d $backup ]; then
    mkdir -p $backup
    chown -R 82.82 $backup
fi
#set -x
cd $backup

root_pwd=$(grep 'MYSQL_ROOT_PASSWORD' /bctos/server/${docker}/docker-compose.yml | sed -r 's/MYSQL_ROOT_PASSWORD://' | sed 's/\\r//;s/\\n//;s/ //g')
if [[ $db_name == '-' ]];then
    db_name="全部数据库"
    docker exec ${docker} sh -c "exec mysqldump --all-databases -uroot -p${root_pwd}" > ${file}
else
    docker exec ${docker} sh -c "exec mysqldump -uroot -p${root_pwd} ${db_name}" > ${file}
fi


zip ${file}.zip ${file} > /dev/null
rm -f ${file}
chmod -R 777 ./*
size=$(ls -lh ${file}.zip | awk '{print $5}')

# 只保留规定的数量
max=$(($logNum+1))
delFiles=''
for var in $(ls -lt | awk -v max=${max} '{if (NR>max){print $9}}')
do
    delFiles="$delFiles $var"
	rm -f $var
done

# 更新备份时间
docker exec panel sh -c "su - www-data -c 'cd /bctos/wwwroot/bctos.cn;php think cron_time ${id} ${size} ${file}.zip \"${delFiles}\"'"

function timediff() {
    start_time=$1
    end_time=$2

    start_s=${start_time%.*}
    start_nanos=${start_time#*.}
    end_s=${end_time%.*}
    end_nanos=${end_time#*.}

    if [ "$end_nanos" -lt "$start_nanos" ];then
        end_s=$(( 10#$end_s - 1 ))
        end_nanos=$(( 10#$end_nanos + 10**9 ))
    fi

    time=$(( 10#$end_s - 10#$start_s )).`printf "%03d\n" $(( (10#$end_nanos - 10#$start_nanos)/10**6 ))`

    echo $time
}
endForam=$(date "+%Y-%m-%d %H:%M:%S")
end=$(date +"%s.%N")

diff=$(timediff $start $end)

echo "
==========================================================================================
+++ 开始备份 [${startForam}]
==========================================================================================
|-备份数据库：${db_name}
|-压缩包大小：$size
|-网站已备份到：$backup/${file}.zip
|-共耗时：${diff} 秒
|-保留最新的备份数：$logNum 份
|-已删除过期备份：${delFiles}
==========================================================================================
--- 备份完成 [${endForam}]
==========================================================================================
\
\
"


#set +x