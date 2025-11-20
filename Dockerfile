FROM php:8.2-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    postgresql-dev \
    mysql-client \
    supervisor

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock ./

# Copy minimal files needed for composer post-autoload-dump
COPY artisan ./
COPY bootstrap/ ./bootstrap/
COPY routes/ ./routes/
COPY config/ ./config/

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copy application files
COPY . .

# Create Firebase storage directory
RUN mkdir -p /var/www/html/storage/firebase && \
    chown -R www-data:www-data /var/www/html/storage/firebase && \
    chmod 755 /var/www/html/storage/firebase

# Copy Firebase credentials if they exist in build context
# Note: File is in .gitignore, so for Git-based builds (like Koyeb), 
# the file won't be in the build context. Use FIREBASE_CREDENTIALS_JSON env var instead.
# For local builds with file present, this will copy it
RUN if [ -d storage/firebase ] && [ -n "$(ls -A storage/firebase/*.json 2>/dev/null)" ]; then \
        cp storage/firebase/*.json /var/www/html/storage/firebase/ && \
        chmod 600 /var/www/html/storage/firebase/*.json && \
        chown www-data:www-data /var/www/html/storage/firebase/*.json && \
        echo "Firebase credentials copied to container"; \
    else \
        echo "Firebase JSON not in build context (expected for Git-based builds - use FIREBASE_CREDENTIALS_JSON env var)"; \
    fi

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Create storage directories
RUN mkdir -p /var/www/html/storage/app/public \
    && mkdir -p /var/www/html/storage/framework/cache \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/storage/logs

# Start script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose port (Koyeb will provide PORT env var)
EXPOSE 8000

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]

