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

	if command -v pg_isready >/dev/null 2>&1; then
		DB_HOST="$(echo "${DATABASE_URL:-}" | sed -n 's|.*@\([^:/?]*\).*|\1|p')"
		DB_PORT="$(echo "${DATABASE_URL:-}" | sed -n 's|.*:\([0-9][0-9]*\)/.*|\1|p')"
		DB_USER="$(echo "${DATABASE_URL:-}" | sed -n 's|.*://\([^:]*\):.*|\1|p')"
		DB_HOST="${DB_HOST:-postgres}"
		DB_PORT="${DB_PORT:-5432}"
		if [ -n "$DB_USER" ]; then
			PG_USER_ARG="-U $DB_USER"
		else
			PG_USER_ARG=""
		fi

		until pg_isready -h "$DB_HOST" -p "$DB_PORT" $PG_USER_ARG >/dev/null 2>&1; do
			(>&2 echo "Waiting for PostgreSQL to be ready at ${DB_HOST}:${DB_PORT}...")
			sleep 1
		done
	else
		(>&2 echo "pg_isready not found, falling back to doctrine probe...")
		until bin/console doctrine:query:sql "select 1" >/dev/null 2>&1; do
			(>&2 echo "Waiting for PostgreSQL to be ready...")
			sleep 1
		done
	fi
	# first run
	echo "migrating..."
	bin/console doctrine:migrations:migrate
fi

exec "$@"
