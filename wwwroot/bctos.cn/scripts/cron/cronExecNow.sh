#!/bin/bash
id=$1

if [[ $id == '' ]];then
    echo "非法访问"
    exit 1
fi

# 查找定时任务命令
line=$(sed -n "/\/bctos\/logs\/cron_$id/p" /var/spool/cron/root | sed -r 's/^.+\/bctos\/wwwroot\/bctos\.cn\/scripts/\/bctos\/wwwroot\/bctos\.cn\/scripts/' | sed 's/ 2>&1//')
eval $line
echo 1;