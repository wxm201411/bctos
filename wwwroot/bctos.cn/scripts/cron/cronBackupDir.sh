#!/bin/bash
start=$(date +"%s.%N")
startForam=$(date "+%Y-%m-%d %H:%M:%S")

path=$1
file=$(date "+%Y%m%d%H%M%S")"_"$(echo $path | sed 's/\//_/g')".zip"
logNum=$2
x=$3
id=$4

backup=/bctos/wwwroot/bctos.cn/public/storage/backup/$id

if [[  !($(which zip) && $(zip -v)) ]]; then
    yum -y install unzip zip > /dev/null
fi

if [ ! -d $backup ]; then
    mkdir -p $backup
    chown -R 82.82 $backup
fi
#set -x
cd $path
line="zip -r ${file} ./* -x \*/public/storage/backup/\* $x"
eval $line > /dev/null
mv ${file} $backup/${file}
cd $backup
chmod -R 755 ./*
size=$(ls -lh ${file} | awk '{print $5}')

# 只保留规定的数量
max=$(($logNum+1))
delFiles=''
for var in $(ls -lt | awk -v max=${max} '{if (NR>max){print $9}}')
do
    delFiles="$delFiles $var"
	rm -f $var
done

# 更新备份时间
docker exec panel sh -c "cd /bctos/wwwroot/bctos.cn;php think cron_time ${id} ${size} ${file} '${delFiles}'"

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
|-备份目录：$path
|-压缩包大小：$size
|-排除设置： -x $x
|-网站已备份到：$backup/${file}
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