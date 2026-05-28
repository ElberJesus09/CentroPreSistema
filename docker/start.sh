#!/usr/bin/env bash
set -e

if [ -n "${DATABASE_URL:-}" ] && [ -z "${DB_URL:-}" ]; then
    export DB_URL="$DATABASE_URL"
fi

if [ -z "${DB_CONNECTION:-}" ] && [ -n "${DB_URL:-}" ]; then
    case "$DB_URL" in
        mysql://*|mysql2://*|mariadb://*)
            export DB_CONNECTION=mysql
            ;;
    esac
fi

envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan db:seed --force

php-fpm -D
nginx -g 'daemon off;'
