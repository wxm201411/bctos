#!/bin/bash
cd $GOPATH/src/github.com/hyperledger/fabric-samples/chaincode-docker-devmode
docker-compose -f docker-compose-simple.yaml down

#kill `ps aux|grep 'web-msg-sender/start_log.php'|grep -v grep|awk '{print $2}'`
#php /www/wwwroot/test.cn/vendor/web-msg-sender/start.php stop
