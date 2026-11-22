#!/usr/bin/env bash
set -e

cd /var/www/html

# Ensure SQLite database file exists if using sqlite driver
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    mkdir -p /var/www/html/database
    touch /var/www/html/database/database.sqlite
fi

# Run migrations and seed initial demo accounts/books safely
php artisan migrate --force
php artisan db:seed --force

# Clear and optimize Laravel caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Starting Library of GMI server on port ${PORT:-10000}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
