#!/bin/sh
# Runs every time the container starts: prepare Laravel, update the database, then serve.
set -e

# Render tells the app which port to use.
PORT="${PORT:-8080}"
sed -ri "s/^Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data storage bootstrap/cache

# Create any missing tables and load the built-in questions, tips and translations.
php artisan migrate --force

# First admin from the ADMIN_EMAIL / ADMIN_PASSWORD settings (never overwrites an existing password).
php artisan matako:ensure-admin || true

# Housekeeping that a scheduler would normally run: expired app sign-ins and old security logs.
php artisan sanctum:prune-expired --hours=24 || true
php artisan model:prune || true

exec apache2-foreground
