#!/bin/bash
alive=`ps aux|grep canal|grep -v grep|wc -l`
if [ $alive -eq 0]
then
nohup php think canal > /dev/null &
fi
