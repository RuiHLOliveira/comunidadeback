#!/usr/bin/env bash

chmod +x bin/console

echo "Running composer"
composer install --no-dev --optimize-autoloader

echo "Caching config..."
# Rodamos o cache:clear, o que cria a pasta var/cache/prod como root
APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear

echo "migrating..."
bin/console doctrine:migrations:migrate

# IMPORTANTE: Garante que o PHP-FPM consiga escrever no cache e logs criados pelo root acima
echo "Fixing permissions..."
chmod -R 777 var/cache var/log
