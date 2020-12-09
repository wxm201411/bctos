#!/bin/bash
id=$1

if [[ $id == '' ]];then
    echo "非法访问"
    exit 1
fi

#删除备份文件
rm -rf /bctos/wwwroot/bctos.cn/public/storage/backup/$id
rm -rf /bctos/wwwroot/bctos.cn/data/custom_shell/${id}".sh"

# 删除日志文件
log="/bctos/logs/cron_${id}.log"
rm -f $log

# 删除定时任务
sed -i "/\/bctos\/logs\/cron_$id/d" /var/spool/cron/root

