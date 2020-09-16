#!/usr/bin/env bash

docker-compose down > /dev/null 2>&1
echo
echo
echo "Hinweis: Docker Services sind gerade online (Up)"
docker ps
echo

echo "Create .env file in project root directory..."
printf "APPUID=%d\nAPPUGID=%d\nDBPORT=%d\nAPPPORT=%d\n" \
$(id -u)    \
$(id -g)    \
"$DBPORT"   \
"$APPPORT"  \
> .env

docker-compose build --parallel --force-rm --no-cache --pull
echo "Finish build images"
docker images | grep "$(basename $PWD)"
docker-compose up -d
docker-compose run -u $(id -u):$(id -g) producer composer install
