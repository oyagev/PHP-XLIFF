#!/usr/bin/env bash

docker-compose up &

echo -n "Waiting for the services to initialize.. "
while [[ ! $(docker ps | grep phpxliff_app_1) ]] ; do
	echo -n "."
	sleep 1
done
echo ""
echo "composer install --prefer-source --no-interaction" |  docker exec -i  phpxliff_app_1 /bin/bash
echo ""
echo "./vendor/bin/phpunit test" |  docker exec -i  phpxliff_app_1 /bin/bash
