#!/bin/bash

cd $GOPATH/src/github.com/hyperledger/fabric-samples/chaincode-docker-devmode

check=$((docker ps | grep "fabric-ccenv" | wc -l) 2>null)
if [[ check -eq 0 ]]; then
  echo "not running"
  exit
fi

docker exec -it chaincode sh
