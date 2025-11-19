# Mazen Maher Chat - Backend API

Laravel 12 backend API for real-time chat application with voice notes support.

## 🚀 Features

- ✅ Username-based authentication (MVP: Only Mazen and Maher)
- ✅ Real-time messaging via Laravel Reverb (WebSocket)
- ✅ Voice notes with compression
- ✅ Read receipts (single/double check)
- ✅ Conversation management
- ✅ Message search
- ✅ Caching for performance
- ✅ Cost optimizations for poor network areas
- ✅ API rate limiting
- ✅ CORS configuration

## 📋 Requirements

- PHP >= 8.2
- Composer
- MySQL/PostgreSQL/SQLite
- Node.js & NPM (for assets)
- Laravel Reverb server

## 🔧 Installation

### 1. Clone Repository
```bash
git clone https://github.com/Nezamrojba/chatsystem.git
cd chatsystem
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure `.env` File
Update the following variables:
```env
APP_NAME="Mazen Maher Chat"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

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
```

### 5. Run Migrations
```bash
php artisan migrate --force
php artisan db:seed
```

### 6. Create Storage Link
```bash
php artisan storage:link
```

### 7. Optimize for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

## 🌐 Production Deployment

### Server Requirements
- PHP 8.2+ with extensions: BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PCRE, PDO, Tokenizer, XML
- MySQL 5.7+ / PostgreSQL 10+ / SQLite 3.8.8+
- Web server (Nginx/Apache)
- SSL certificate (HTTPS required)

### Deployment Steps

1. **Upload Files**
   ```bash
   # Exclude vendor, node_modules, .env
   rsync -avz --exclude='vendor' --exclude='node_modules' --exclude='.env' . user@server:/path/to/app
   ```

2. **Install Dependencies**
   ```bash
   composer install --no-dev --optimize-autoloader
   npm install && npm run build
   ```

3. **Set Permissions**
   ```bash
   chmod -R 755 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

4. **Configure Web Server**
   - Point document root to `public/` directory
   - Enable HTTPS
   - Configure CORS for your frontend domain

5. **Start Laravel Reverb**
   ```bash
   php artisan reverb:start --host=0.0.0.0 --port=8080
   ```
   Or use a process manager (Supervisor, PM2):
   ```ini
   [program:reverb]
   command=php /path/to/app/artisan reverb:start --host=0.0.0.0 --port=8080
   autostart=true
   autorestart=true
   user=www-data
   ```

6. **Queue Worker** (if using queues)
   ```bash
   php artisan queue:work --tries=3
   ```

## 🔐 Security Checklist

- [ ] Set `APP_DEBUG=false` in production
- [ ] Use strong `APP_KEY` (generated with `php artisan key:generate`)
- [ ] Use HTTPS for all connections
- [ ] Configure CORS for your frontend domain only
- [ ] Use strong database passwords
- [ ] Set secure Reverb keys
- [ ] Enable rate limiting
- [ ] Review file permissions
- [ ] Use environment variables for secrets
- [ ] Enable firewall rules

## 📡 API Endpoints

### Authentication
- `POST /api/login` - Login with username/password
- `POST /api/logout` - Logout user
- `GET /api/user` - Get current user

### Conversations
- `GET /api/conversations` - List user conversations
- `POST /api/conversations` - Create conversation
- `GET /api/conversations/{id}` - Get conversation details
- `GET /api/conversations/{id}/messages` - Get conversation messages

### Messages
- `POST /api/messages` - Send message (text or voice)
- `GET /api/messages/search` - Search messages

## 🔄 Laravel Reverb Setup

1. **Generate Reverb Keys**
   ```bash
   php artisan reverb:install
   ```

2. **Start Reverb Server**
   ```bash
   php artisan reverb:start
   ```

3. **Production Configuration**
   - Use Supervisor or PM2 to keep Reverb running
   - Configure reverse proxy (Nginx) for WebSocket
   - Use SSL/TLS for secure connections

### Nginx Configuration Example
```nginx
location /app/ {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
}
```

## 🗄️ Database

### Migrations
```bash
php artisan migrate
```

### Seeders
```bash
php artisan db:seed
```

Creates initial users:
- Mazen (username: `Mazen`, password: `password`)
- Maher (username: `Maher`, password: `password`)

**⚠️ Change passwords in production!**

## 📦 Caching

The app uses database caching by default. For better performance:

1. **Redis** (Recommended)
   ```env
   CACHE_STORE=redis
   REDIS_HOST=127.0.0.1
   REDIS_PORT=6379
   ```

2. **Memcached**
   ```env
   CACHE_STORE=memcached
   ```

## 🔧 Maintenance

### Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Optimize
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Logs
Logs are stored in `storage/logs/laravel.log`

## 🧪 Testing

```bash
php artisan test
```

## 📝 Environment Variables

See `.env.example` for all available environment variables.

### Required for Production:
- `APP_KEY` - Application encryption key
- `DB_*` - Database configuration
- `REVERB_*` - Reverb WebSocket configuration
- `APP_URL` - Application URL

## 🐛 Troubleshooting

### Reverb Not Connecting
- Check Reverb server is running
- Verify REVERB_* environment variables
- Check firewall/port accessibility
- Verify CORS configuration

### Storage Issues
- Run `php artisan storage:link`
- Check `storage/` directory permissions
- Verify `FILESYSTEM_DISK` in `.env`

### Database Issues
- Verify database credentials
- Check database exists
- Run migrations: `php artisan migrate`

## 📄 License

MIT License

## 👥 Authors

- Mazen
- Maher

