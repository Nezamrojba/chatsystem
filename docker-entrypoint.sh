#!/bin/sh
set -e

echo "Starting application setup..."

# Parse Railway MySQL configuration
if [ -z "$DB_HOST" ]; then
    # Try Railway individual variables first
    if [ -n "$MYSQLHOST" ] || [ -n "$MYSQL_HOST" ]; then
        echo "Detected Railway MySQL individual variables..."
        export DB_CONNECTION=${DB_CONNECTION:-mysql}
        export DB_HOST=${MYSQLHOST:-$MYSQL_HOST}
        export DB_PORT=${MYSQLPORT:-${MYSQL_PORT:-3306}}
        export DB_DATABASE=${MYSQLDATABASE:-${MYSQL_DATABASE:-railway}}
        export DB_USERNAME=${MYSQLUSER:-${MYSQL_USER:-root}}
        export DB_PASSWORD=${MYSQLPASSWORD:-${MYSQL_PASSWORD:-$MYSQL_ROOT_PASSWORD}}
        echo "MySQL connection configured from Railway variables: $DB_HOST:$DB_PORT/$DB_DATABASE"
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
    fi
fi

# Wait for database to be ready (with shorter timeout, non-blocking)
if [ -n "$DB_HOST" ] || [ -n "$MYSQL_HOST" ] || [ -n "$MYSQLHOST" ]; then
    echo "Checking database connection..."
    timeout=10
    counter=0
    until php artisan db:show > /dev/null 2>&1 || [ $counter -ge $timeout ]; do
        echo "Database is unavailable - sleeping ($counter/$timeout)"
        sleep 1
        counter=$((counter + 1))
    done
    
    if [ $counter -ge $timeout ]; then
        echo "Warning: Database connection timeout. Starting app anyway - migrations will run in background..."
    else
        echo "Database is up!"
        # Run migrations if database is available
        echo "Running migrations..."
        php artisan migrate --force || echo "Migration failed, continuing..."
    fi
else
    echo "No database configuration found, skipping database setup"
fi

# Create storage link
php artisan storage:link || echo "Storage link already exists or failed"

# Cache configuration (only in production) - run in background to not block startup
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

