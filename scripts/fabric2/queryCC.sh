#!/bin/bash
# 测试命令
# ./queryCC.sh

#加载公共函数库
. $(cd `dirname $0`; pwd)/common.sh
# 切换到测试网络
cd $GOPATH/src/github.com/hyperledger/fabric-samples/test-network

# 切换到组织1的peer节点上
Org1

#先在组织1的peer安装
echo "$(peer lifecycle chaincode queryinstalled | sed '1d' )"