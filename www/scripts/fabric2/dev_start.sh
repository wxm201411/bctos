#!/bin/bash
cd $GOPATH/src/github.com/hyperledger/fabric-samples/chaincode-docker-devmode

check=$((docker ps | grep "fabric-ccenv" | wc -l) 2>null)
if [[ check -eq 0 ]]; then
  docker-compose -f docker-compose-simple.yaml up
fi


