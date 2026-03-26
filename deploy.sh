#!/bin/bash
echo "=== Railway Deploy ==="

# Clear build-phase cache
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/routes-v7.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/packages.php
rm -f bootstrap/cache/events.php

# Run composer scripts skipped during build (package:discover needs DB)
composer dump-autoload --optimize 2>/dev/null || true
php artisan package:discover --ansi 2>/dev/null || true

# Check if this is a first deploy by attempting a safe artisan command
if php artisan migrate:status > /dev/null 2>&1; then
    echo "Running migrations..."
    php artisan migrate --force || echo "WARNING: migrate failed"
else
    echo "First deploy — running migrations and seeding..."
    php artisan migrate --force || { echo "ERROR: migrate failed"; exit 1; }
    php artisan db:seed --force || echo "WARNING: db:seed failed"
    php artisan b2b-suite:install || echo "WARNING: b2b-suite:install failed"
    php artisan db:seed --class="Rydeen\Core\Database\Seeders\RydeenSeeder" --force || echo "WARNING: Rydeen seed failed"
fi

php artisan storage:link --force || true
touch storage/installed

# Ensure Rydeen branding is applied
php artisan db:seed --class="Rydeen\Core\Database\Seeders\RydeenSeeder" --force || echo "WARNING: Rydeen seed failed"

php artisan optimize || echo "WARNING: optimize failed"

# One-time test email — remove after confirming Resend works
echo "=== Sending test email ==="
php artisan rydeen:test-email zacharyamith@outlook.com || echo "WARNING: test email failed"

echo "=== Starting Octane (FrankenPHP) on port ${PORT:-8080} ==="
php artisan octane:install --server=frankenphp 2>&1 || { echo "ERROR: FrankenPHP install failed"; exit 1; }
# Ensure errors are logged to stderr
export LOG_CHANNEL=stderr
export LOG_LEVEL=debug

# Remove broken Nix opcache extension that crashes FrankenPHP's embedded PHP
OPCACHE_SO="/nix/store/zj253anmmfqr6l5cp1l53qflkxr4cvv5-php-opcache-8.2.27/lib/php/extensions/opcache.so"
if [ -f "$OPCACHE_SO" ]; then
    # Find and disable the ini file that loads it
    for ini_dir in $(php -r "echo php_ini_scanned_dir();" 2>/dev/null) /etc/php.d /etc/php/conf.d; do
        if [ -d "$ini_dir" ]; then
            find "$ini_dir" -name '*opcache*' -exec sh -c 'echo "; DISABLED — incompatible with FrankenPHP" > "$1"' _ {} \; 2>/dev/null || true
        fi
    done
    echo "Disabled broken opcache extension"
fi

exec php artisan octane:start --server=frankenphp --host=0.0.0.0 --port=${PORT:-8080} --workers=4 --max-requests=500
