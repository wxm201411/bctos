#!/bin/bash
# 测试命令
# ./deployCC.sh ca mychannel $GOPATH/src/bsnBaseCC/ clockin golang
#set -x
if [[ $5 == "golang" ]]; then
  cd $3
  GO111MODULE=on go mod vendor
fi

#加载公共函数库
. $(cd $(dirname $0); pwd)/common.sh

# 切换到测试网络
cd $GOPATH/src/github.com/hyperledger/fabric-samples/test-network

export GOPROXY=https://goproxy.io
echo "========GOPROXY======"
echo $GOPROXY

# 使用默认的通道名称
export CHANNEL_NAME=$2
echo "========CHANNEL_NAME======"
echo $CHANNEL_NAME
export PACKNAME=$4
echo "========PACKNAME_PATH======"
echo $3

# 切换到组织1的peer节点上
Org1

#先在组织1的peer安装
peer lifecycle chaincode package $PACKNAME.tar.gz --path $3 --lang $5 --label $PACKNAME
if [[ $? -ne 0 ]]; then
  echo "打包链码失败"
  exit 1
fi

echo "peer lifecycle chaincode install $PACKNAME.tar.gz"
peer lifecycle chaincode install $PACKNAME.tar.gz
if [[ $? -ne 0 ]]; then
  echo "安装链码失败"
  exit 1
fi

export CC_PACKAGE_ID=$(peer lifecycle chaincode queryinstalled | sed '1d' | sed 's/Package ID: //' | sed 's/,.*//g')
#export CC_PACKAGE_ID=clockin:0d61c476742be4182cc817ea2b96380aab73f6725d2f4b129074c428a2aff24a
echo "========CC_PACKAGE_ID======"
echo $CC_PACKAGE_ID
#再切换到组织2的peer上执行相同的安装操作
Org2
peer lifecycle chaincode install $PACKNAME.tar.gz

echo "####################################################################################"
echo "######### 设置背书策略 ###########################"
echo "####################################################################################"

peer lifecycle chaincode approveformyorg $ORDER_CONN_PARMS --channelID $CHANNEL_NAME --name $PACKNAME --version 1 --init-required --package-id $CC_PACKAGE_ID --sequence 1

Org1

peer lifecycle chaincode approveformyorg $ORDER_CONN_PARMS --channelID $CHANNEL_NAME --name $PACKNAME --version 1 --init-required --package-id $CC_PACKAGE_ID --sequence 1

echo "####################################################################################"
echo "######### 把链码提交到通道上 ###########################"
echo "####################################################################################"

peer lifecycle chaincode commit $ORDER_CONN_PARMS -C $CHANNEL_NAME -n $PACKNAME -v 1 --sequence 1 $PEER_CONN_PARMS --init-required

peer lifecycle chaincode querycommitted -C $CHANNEL_NAME -n $PACKNAME

#set +x