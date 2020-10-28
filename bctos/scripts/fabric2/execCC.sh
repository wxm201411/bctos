#!/bin/bash
# 测试命令
# ./execCC.sh instantiate fabcar '{"function":"initLedger","Args":[]}' mychannel
# ./execCC.sh query fabcar '{"Args":["queryAllCars"]}' mychannel

export FUNC=$1
export CHAIN_CODE=$2
export FUNC_PARAM=$3
export CHANNEL_NAME=$4

#加载公共函数库
. $(cd `dirname $0`; pwd)/common.sh

# 切换到测试网络
cd $GOPATH/src/github.com/hyperledger/fabric-samples/test-network

# 切换到组织1的peer节点上
Org1
#set -x
#echo $3
if [[ $FUNC == "query" ]]; then
  #查询
  echo "$( peer chaincode query -C $CHANNEL_NAME -n $CHAIN_CODE -c "$FUNC_PARAM" 2>&1)"
elif [[ $FUNC == "invoke" ]]; then
  #更新
  echo "$( peer chaincode invoke $ORDER_CONN_PARMS -C $CHANNEL_NAME -n $CHAIN_CODE $PEER_CONN_PARMS -c "$FUNC_PARAM" 2>&1)"
else
  #初始化
  echo "$( peer chaincode invoke $ORDER_CONN_PARMS -C $CHANNEL_NAME -n $CHAIN_CODE $PEER_CONN_PARMS --isInit -c "$FUNC_PARAM" 2>&1)"
fi
#set +x