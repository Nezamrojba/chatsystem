#!/bin/sh
set -e

echo "Starting application setup..."

# Wait for database to be ready
if [ -n "$DB_HOST" ]; then
    echo "Checking database connection..."
    timeout=30
    counter=0
    
    # Detect database type and test connection
    DB_TYPE=${DB_CONNECTION:-pgsql}
    if [ "$DB_TYPE" = "pgsql" ] || [ "$DB_TYPE" = "postgres" ]; then
        # PostgreSQL connection test
        until php -r "
            try {
                \$dsn = 'pgsql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname='.getenv('DB_DATABASE');
                \$pdo = new PDO(\$dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                \$pdo->query('SELECT 1');
                exit(0);
            } catch (Exception \$e) {
                exit(1);
            }
        " 2>/dev/null || [ $counter -ge $timeout ]; do
            echo "Database is unavailable - sleeping ($counter/$timeout)"
            sleep 2
            counter=$((counter + 2))
        done
    else
        # MySQL connection test
        until php -r "
            try {
                \$pdo = new PDO('mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
                \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                \$pdo->query('SELECT 1');
                exit(0);
            } catch (Exception \$e) {
                exit(1);
            }
        " 2>/dev/null || [ $counter -ge $timeout ]; do
            echo "Database is unavailable - sleeping ($counter/$timeout)"
            sleep 2
            counter=$((counter + 2))
        done
    fi
    
    if [ $counter -ge $timeout ]; then
        echo "Warning: Database connection timeout after ${timeout}s. Starting app anyway - migrations will run in background..."
        # Try to run migrations in background anyway
        (sleep 5 && php artisan migrate --force && php artisan db:seed --force || echo "Background migration/seed failed") &
    else
        echo "Database is up!"
        # Run migrations if database is available
        echo "Running migrations..."
        php artisan migrate --force || echo "Migration failed, continuing..."
        
        # Run seeders after migrations
        echo "Running database seeders..."
        php artisan db:seed --force || echo "Seeding failed, continuing..."
    fi
else
    echo "No database configuration found, skipping database setup"
fi

# Create storage link
php artisan storage:link || echo "Storage link already exists or failed"

# Cache configuration (only in production)
if [ "$APP_ENV" = "production" ]; then
    echo "Caching configuration..."
    # Clear ALL caches first to avoid stale config
    php artisan config:clear 2>/dev/null || true
    php artisan route:clear 2>/dev/null || true
    php artisan view:clear 2>/dev/null || true
    
    # Set BROADCAST_CONNECTION to null if not set and Reverb not configured
    if [ -z "$BROADCAST_CONNECTION" ] && [ -z "$REVERB_APP_KEY" ]; then
        export BROADCAST_CONNECTION=null
    fi
    
    # Cache config (will use null broadcaster if Reverb not configured)
    php artisan config:cache || echo "Config cache failed (continuing anyway)"
    php artisan route:cache || echo "Route cache failed (continuing anyway)"
    php artisan view:cache || echo "View cache failed (continuing anyway)"
    
    # Skip event cache if Reverb not configured (events might try to broadcast)
    if [ -n "$REVERB_APP_KEY" ]; then
        php artisan event:cache || echo "Event cache failed (continuing anyway)"
    else
        echo "Skipping event cache (Reverb not configured - this is OK)"
    fi
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
