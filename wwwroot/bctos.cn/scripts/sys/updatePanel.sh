#!/bin/bash
cd /bctos

showMsg(){
    echo "########################### $1 ###########################"
}
showMsg "更新代码"
git fetch --all
git reset --hard origin/master
git pull
