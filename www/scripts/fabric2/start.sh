#!/bin/bash
cd $GOPATH/src/github.com/hyperledger/fabric-samples/test-network
./network.sh down

if [[ $1 == "ca" ]]; then
  ./network.sh up -ca
else
  ./network.sh up
fi

./network.sh createChannel -c $2
