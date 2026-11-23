#!/usr/bin/env bash
set -e

cd /var/www/html

# Create .env from .env.example if .env does not exist
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Generate APP_KEY automatically if not provided via Render Environment Variables
if [ -z "${APP_KEY}" ]; then
    php artisan key:generate --force
fi

# Ensure SQLite database file exists if using sqlite driver
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    mkdir -p /var/www/html/database
    touch /var/www/html/database/database.sqlite
    chmod -R 775 /var/www/html/database /var/www/html/storage /var/www/html/bootstrap/cache
fi

# Clear any stale config before running migrations
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run migrations and seed initial demo accounts/books safely
php artisan migrate --force
php artisan db:seed --force

echo "Starting Library of GMI server on port ${PORT:-10000}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
