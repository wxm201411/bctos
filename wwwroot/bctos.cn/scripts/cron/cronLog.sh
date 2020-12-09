#!/bin/bash
id=$1

if [[ $id == '' ]];then
    echo "非法访问"
    exit 1
fi

# 查找定时任务命令
if [ -f "/bctos/logs/cron_${id}.log" ];then
    cat /bctos/logs/cron_${id}.log
else
    echo "还没有生成的日志，建议先执行再看"
fi
