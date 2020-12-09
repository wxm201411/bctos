#!/bin/bash
start=$(date +"%s.%N")
startForam=$(date "+%Y-%m-%d %H:%M:%S")

id=$1

if [[  ! $(chronyc sourcestats -v) ]]; then
    echo "=====yum -y install ntpdate========"
    yum -y install chrony
    systemctl enable chronyd
    systemctl start chronyd
fi

chronyc sourcestats -v > /dev/null

# 更新备份时间
docker exec panel sh -c "cd /bctos/wwwroot/bctos.cn;php think cron_time ${id} 0 cronTime"

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
+++ 开始执行 [${startForam}]
==========================================================================================
|-时间同步完成，共耗时：${diff} 秒
==========================================================================================
--- 执行结束 [${endForam}]
==========================================================================================
\
\
"