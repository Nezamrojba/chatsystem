#!/bin/sh
set -e

echo "Starting application setup..."

# Wait for database to be ready (with timeout)
if [ -n "$DB_HOST" ]; then
    echo "Waiting for database connection..."
    timeout=30
    counter=0
    until php artisan db:show > /dev/null 2>&1 || [ $counter -ge $timeout ]; do
        echo "Database is unavailable - sleeping ($counter/$timeout)"
        sleep 1
        counter=$((counter + 1))
    done
    
    if [ $counter -ge $timeout ]; then
        echo "Warning: Database connection timeout. Continuing anyway..."
    else
        echo "Database is up!"
    fi
fi

# Run migrations (only if database is available)
if php artisan db:show > /dev/null 2>&1; then
    echo "Running migrations..."
    php artisan migrate --force || echo "Migration failed, continuing..."
else
    echo "Skipping migrations (database not available)"
fi

# Create storage link
php artisan storage:link || echo "Storage link already exists or failed"

# Cache configuration (only in production)
if [ "$APP_ENV" = "production" ]; then
    echo "Caching configuration..."
    php artisan config:cache || echo "Config cache failed"
    php artisan route:cache || echo "Route cache failed"
    php artisan view:cache || echo "View cache failed"
    php artisan event:cache || echo "Event cache failed"
fi

# Start Laravel Reverb in background if REVERB_APP_KEY is set
if [ -n "$REVERB_APP_KEY" ]; then
    echo "Starting Laravel Reverb..."
    php artisan reverb:start --host=0.0.0.0 --port=8080 > /dev/null 2>&1 &
    echo "Reverb started in background"
fi

echo "Setup complete. Starting application..."

# Execute main command
exec "$@"

