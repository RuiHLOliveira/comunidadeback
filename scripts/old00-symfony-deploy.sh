#!/bin/sh
set -e

if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'bin/console' ]; then
    composer install --prefer-dist --no-progress --no-suggest --no-interaction
    bin/console assets:install --no-interaction

	# first run
	# echo "creating database..."
	# bin/console doctrine:database:create --if-not-exists

	until bin/console doctrine:query:sql "select 1" >/dev/null 2>&1; do
	    (>&2 echo "Waiting for PostgreSql to be ready...")
		sleep 1
	done
	# first run
	# echo "migrating..."
	# bin/console doctrine:migrations:migrate
fi

exec "$@"