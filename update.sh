#!/usr/bin/env bash

start=$SECONDS
git pull
composer install --optimize-autoloader --dev
# Update and Backup database
php app/console assetic:dump -e=prod web
php app/console cache:cl -e=prod
php app/console cache:warmup -e=prod
phpunit -c app/phpunit.xml.dist

duration=$(( SECONDS - start ))

echo "Update took ${duration} seconds."
