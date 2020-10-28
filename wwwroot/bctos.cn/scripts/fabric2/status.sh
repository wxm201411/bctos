#!/bin/bash
if ! [[ $(command -v peer) ]]; then
  echo "not install"
  exit
fi

check=$((docker ps | grep "fabric-peer" | wc -l) 2>null)
if [[ check -eq 0 ]]; then
  echo "not running"
  exit
fi

echo "is running"