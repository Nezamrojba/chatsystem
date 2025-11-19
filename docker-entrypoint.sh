#!/bin/sh
set -e

echo "Starting application setup..."

# Debug: Show available MySQL-related environment variables
echo "Checking for MySQL variables..."
echo "MYSQLHOST=${MYSQLHOST:-not set}"
echo "MYSQL_HOST=${MYSQL_HOST:-not set}"
echo "MYSQL_URL=${MYSQL_URL:-not set}"
echo "DB_HOST=${DB_HOST:-not set}"

# Parse Railway MySQL configuration
# Check if DB_HOST is already set, if not, try to get from Railway variables
if [ -z "$DB_HOST" ]; then
    # Try Railway individual variables first (most reliable)
    if [ -n "$MYSQLHOST" ]; then
        echo "Detected Railway MySQL individual variables (MYSQLHOST)..."
        export DB_CONNECTION=${DB_CONNECTION:-mysql}
        export DB_HOST=$MYSQLHOST
        export DB_PORT=${MYSQLPORT:-3306}
        export DB_DATABASE=${MYSQLDATABASE:-railway}
        export DB_USERNAME=${MYSQLUSER:-root}
        export DB_PASSWORD=${MYSQLPASSWORD:-$MYSQL_ROOT_PASSWORD}
        echo "MySQL connection configured from Railway variables: $DB_HOST:$DB_PORT/$DB_DATABASE"
    # Fallback to MYSQL_HOST (alternative variable name)
    elif [ -n "$MYSQL_HOST" ]; then
        echo "Detected Railway MySQL variables (MYSQL_HOST)..."
        export DB_CONNECTION=${DB_CONNECTION:-mysql}
        export DB_HOST=$MYSQL_HOST
        export DB_PORT=${MYSQL_PORT:-3306}
        export DB_DATABASE=${MYSQL_DATABASE:-railway}
        export DB_USERNAME=${MYSQL_USER:-root}
        export DB_PASSWORD=${MYSQL_PASSWORD:-$MYSQL_ROOT_PASSWORD}
        echo "MySQL connection configured: $DB_HOST:$DB_PORT/$DB_DATABASE"
    # Fallback to parsing MYSQL_URL
    elif [ -n "$MYSQL_URL" ]; then
        echo "Detected Railway MySQL URL, parsing connection details..."
        # MYSQL_URL format: mysql://user:password@host:port/database
        MYSQL_USER=$(echo "$MYSQL_URL" | sed -n 's|.*://\([^:]*\):.*|\1|p')
        MYSQL_PASS=$(echo "$MYSQL_URL" | sed -n 's|.*://[^:]*:\([^@]*\)@.*|\1|p')
        MYSQL_HOST=$(echo "$MYSQL_URL" | sed -n 's|.*@\([^:]*\):.*|\1|p')
        MYSQL_PORT=$(echo "$MYSQL_URL" | sed -n 's|.*:\([0-9]*\)/.*|\1|p')
        MYSQL_DB=$(echo "$MYSQL_URL" | sed -n 's|.*/\([^?]*\).*|\1|p')
        
        export DB_CONNECTION=${DB_CONNECTION:-mysql}
        export DB_HOST=$MYSQL_HOST
        export DB_PORT=${MYSQL_PORT:-3306}
        export DB_DATABASE=$MYSQL_DB
        export DB_USERNAME=$MYSQL_USER
        export DB_PASSWORD=$MYSQL_PASS
        echo "MySQL connection configured from Railway URL: $MYSQL_HOST:$MYSQL_PORT/$MYSQL_DB"
    else
        echo "No Railway MySQL variables found (MYSQLHOST, MYSQL_HOST, or MYSQL_URL)"
    fi
else
    echo "DB_HOST already set to: $DB_HOST"
fi

# Wait for database to be ready (with longer timeout for Railway)
if [ -n "$DB_HOST" ]; then
    echo "Checking database connection..."
    timeout=30
    counter=0
    # Try simple connection test using mysql client or php
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
    # Clear old config cache first to avoid stale 'reverb' default
    php artisan config:clear 2>/dev/null || true
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

