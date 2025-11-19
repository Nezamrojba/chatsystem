# Production Deployment Guide

## 🚀 Quick Deployment Checklist

### 1. Server Setup
- [ ] PHP 8.2+ installed with required extensions
- [ ] MySQL/PostgreSQL database created
- [ ] Composer installed
- [ ] Node.js & NPM installed
- [ ] Web server (Nginx/Apache) configured
- [ ] SSL certificate installed

### 2. Application Setup
```bash
# Clone repository
git clone https://github.com/Nezamrojba/chatsystem.git
cd chatsystem

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# Configure environment
cp .env.example .env
# Edit .env with your production values
php artisan key:generate

# Run migrations
php artisan migrate --force

# Seed database (creates Mazen and Maher users)
php artisan db:seed

# Create storage link
php artisan storage:link

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 3. Environment Variables (.env)

**Required:**
```env
APP_NAME="Mazen Maher Chat"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:... (generate with php artisan key:generate)
APP_URL=https://your-api-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chat_system
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

REVERB_APP_ID=your-reverb-app-id
REVERB_APP_KEY=your-reverb-app-key
REVERB_APP_SECRET=your-reverb-app-secret
REVERB_HOST=your-reverb-host.com
REVERB_PORT=443
REVERB_SCHEME=https

FRONTEND_URL=https://your-frontend-domain.com
```

### 4. Laravel Reverb Setup

**Start Reverb Server:**
```bash
php artisan reverb:start --host=0.0.0.0 --port=8080
```

**Using Supervisor (Recommended):**
Create `/etc/supervisor/conf.d/reverb.conf`:
```ini
[program:reverb]
command=php /path/to/app/artisan reverb:start --host=0.0.0.0 --port=8080
directory=/path/to/app
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/path/to/app/storage/logs/reverb.log
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start reverb
```

### 5. Nginx Configuration

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-api-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-api-domain.com;

    ssl_certificate /path/to/ssl/cert.pem;
    ssl_certificate_key /path/to/ssl/key.pem;

    root /path/to/app/public;
    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # WebSocket for Reverb
    location /app/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 6. Queue Worker (Optional)

If using queues:
```bash
php artisan queue:work --tries=3
```

Or with Supervisor:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/app/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/app/storage/logs/worker.log
```

### 7. Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] Strong `APP_KEY` generated
- [ ] HTTPS enabled
- [ ] CORS configured for frontend domain only
- [ ] Strong database passwords
- [ ] Secure Reverb keys
- [ ] File permissions set correctly (755 for directories, 644 for files)
- [ ] `.env` file not accessible via web
- [ ] Rate limiting enabled
- [ ] Firewall configured

### 8. Testing

After deployment, test:
- [ ] API endpoints respond correctly
- [ ] Authentication works
- [ ] WebSocket connection (Reverb) works
- [ ] File uploads (voice notes) work
- [ ] Database connections work
- [ ] CORS allows frontend requests

### 9. Monitoring

- Check logs: `tail -f storage/logs/laravel.log`
- Monitor Reverb: Check supervisor status
- Monitor queue: Check queue worker status
- Monitor resources: CPU, memory, disk

### 10. Updates

When updating:
```bash
git pull origin main
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

## 🔧 Troubleshooting

### Reverb Not Connecting
- Check Reverb is running: `sudo supervisorctl status reverb`
- Verify REVERB_* environment variables
- Check firewall allows port 8080
- Verify Nginx WebSocket proxy configuration

### 500 Errors
- Check logs: `tail -f storage/logs/laravel.log`
- Verify file permissions
- Clear caches: `php artisan config:clear`
- Check `.env` file exists and is configured

### Database Errors
- Verify database credentials in `.env`
- Check database exists
- Run migrations: `php artisan migrate`

### CORS Errors
- Update `FRONTEND_URL` in `.env`
- Clear config cache: `php artisan config:clear && php artisan config:cache`

## 📞 Support

For issues, check:
1. Laravel logs: `storage/logs/laravel.log`
2. Reverb logs: `storage/logs/reverb.log`
3. Nginx error logs: `/var/log/nginx/error.log`

