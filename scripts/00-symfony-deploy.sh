#!/usr/bin/env bash

chmod +x bin/console

echo "Running composer"
composer install --no-dev --optimize-autoloader

echo "Caching config..."
APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear

# echo "creating database..."
# bin/console doctrine:database:create --if-not-exists

echo "migrating..."
bin/console doctrine:migrations:migrate