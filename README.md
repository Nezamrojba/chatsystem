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
- ✅ Docker support for Render deployment

## 🐳 Docker Deployment (Render)

### Quick Start on Render

1. **Connect Repository**
   - Go to [Render Dashboard](https://dashboard.render.com)
   - Click "New +" → "Web Service"
   - Connect your GitHub repository: `Nezamrojba/chatsystem`

2. **Configure Service**
   - **Name**: `chat-backend-api`
   - **Environment**: `Docker`
   - **Dockerfile Path**: `./Dockerfile`
   - **Docker Context**: `.`
   - **Plan**: Starter (or higher)

3. **Environment Variables**
   Set these in Render dashboard:
   ```env
   APP_NAME=Mazen Maher Chat
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=base64:... (generate with: php artisan key:generate)
   APP_URL=https://your-service.onrender.com
   
   DB_CONNECTION=pgsql
   DB_HOST=your-postgres-host
   DB_PORT=5432
   DB_DATABASE=your-database-name
   DB_USERNAME=your-db-user
   DB_PASSWORD=your-db-password
   
   REVERB_APP_ID=your-reverb-app-id
   REVERB_APP_KEY=your-reverb-app-key
   REVERB_APP_SECRET=your-reverb-app-secret
   REVERB_HOST=your-reverb-service.onrender.com
   REVERB_PORT=443
   REVERB_SCHEME=https
   
   FRONTEND_URL=https://your-frontend-url.com
   
   CACHE_STORE=database
   SESSION_DRIVER=database
   QUEUE_CONNECTION=database
   BROADCAST_CONNECTION=reverb
   ```

4. **Database Setup**
   - Create a PostgreSQL database on Render
   - Copy connection details to environment variables
   - Database will be migrated automatically on first deploy

5. **Deploy**
   - Render will automatically build and deploy
   - Check logs for any issues
   - Service will be available at: `https://your-service.onrender.com`

### Using render.yaml (Optional)

The repository includes `render.yaml` for automatic configuration. Render will use it if present.

## 📋 Local Development

### Requirements
- PHP >= 8.2
- Composer
- MySQL/PostgreSQL/SQLite
- Node.js & NPM

### Installation

1. **Clone Repository**
   ```bash
   git clone https://github.com/Nezamrojba/chatsystem.git
   cd chatsystem
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure `.env`**
   ```env
   APP_NAME="Mazen Maher Chat"
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://localhost:8000
   
   DB_CONNECTION=sqlite
   # Or use MySQL/PostgreSQL
   ```

5. **Run Migrations**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Create Storage Link**
   ```bash
   php artisan storage:link
   ```

7. **Start Development Server**
   ```bash
   # Start Laravel
   php artisan serve
   
   # Start Reverb (in another terminal)
   php artisan reverb:start
   
   # Build assets (in another terminal)
   npm run dev
   ```

## 🐳 Docker Development

### Build and Run
```bash
# Build image
docker build -t chat-backend .

# Run container
docker run -p 8000:8000 --env-file .env chat-backend
```

### Docker Compose (Optional)
```bash
docker-compose up -d
```

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

### Local Development
```bash
php artisan reverb:start
```

### Production (Render)
Reverb starts automatically via Docker entrypoint. Ensure:
- `REVERB_APP_KEY`, `REVERB_APP_SECRET`, `REVERB_APP_ID` are set
- Reverb service is accessible on port 8080
- WebSocket proxy is configured (Render handles this automatically)

### Generate Reverb Keys
```bash
php artisan reverb:install
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
- **Mazen** (username: `Mazen`, password: `password`)
- **Maher** (username: `Maher`, password: `password`)

**⚠️ Change passwords in production!**

## 📦 Caching

Default: Database caching. For better performance:

### Redis (Recommended)
```env
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### Memcached
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

### Optimize for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Logs
Logs: `storage/logs/laravel.log`

## 🔐 Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] Strong `APP_KEY` (generate with `php artisan key:generate`)
- [ ] HTTPS enabled
- [ ] CORS configured for frontend domain only
- [ ] Strong database passwords
- [ ] Secure Reverb keys
- [ ] Rate limiting enabled
- [ ] Environment variables for secrets

## 🐛 Troubleshooting

### Reverb Not Connecting
- Check Reverb server is running
- Verify `REVERB_*` environment variables
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

### Docker Issues
- Check Docker logs: `docker logs <container-id>`
- Verify environment variables
- Check database connectivity

## 📝 Environment Variables

See `.env.example` for all available variables.

### Required for Production:
- `APP_KEY` - Application encryption key
- `DB_*` - Database configuration
- `REVERB_*` - Reverb WebSocket configuration
- `APP_URL` - Application URL
- `FRONTEND_URL` - Frontend URL for CORS

## 🧪 Testing

```bash
php artisan test
```

## 📄 License

MIT License

## 👥 Authors

- Mazen
- Maher
