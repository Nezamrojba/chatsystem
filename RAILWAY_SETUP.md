# Railway Setup Guide

## Environment Configuration

### 1. Service Setup

In Railway, you need **two services** in the same environment:

1. **MySQL Database Service**
   - Add MySQL service from Railway's template
   - Railway automatically provides connection variables

2. **Web Service (Your Laravel API)**
   - Connect your GitHub repository
   - Railway will auto-detect Dockerfile

### 2. Environment Variables

Railway automatically provides these MySQL variables when services are in the same environment:

- `MYSQL_URL` - Full connection string
- `MYSQLHOST` - Database host
- `MYSQLPORT` - Database port (3306)
- `MYSQLDATABASE` - Database name
- `MYSQLUSER` - Database user
- `MYSQLPASSWORD` - Database password
- `MYSQL_ROOT_PASSWORD` - Root password

**You don't need to manually set these** - Railway provides them automatically when MySQL service is in the same environment.

### 3. Required Manual Environment Variables

Set these in your **Web Service** environment variables:

```env
APP_NAME=Mazen Maher Chat
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:... (generate with: php artisan key:generate)
APP_URL=https://your-service.railway.app

DB_CONNECTION=mysql
# DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
# are automatically set from Railway MySQL service

CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=your-reverb-app-id
REVERB_APP_KEY=your-reverb-app-key
REVERB_APP_SECRET=your-reverb-app-secret
REVERB_HOST=your-reverb-host
REVERB_PORT=443
REVERB_SCHEME=https

FRONTEND_URL=https://your-frontend-url.com
```

### 4. Service Linking

**Important**: Make sure your Web Service is linked to the MySQL service:

1. Go to your Web Service
2. Click "Settings"
3. Under "Service Connections" or "Dependencies"
4. Link to your MySQL service

This ensures Railway provides the MySQL connection variables automatically.

### 5. Health Check

Set the health check path in Railway:
- **Health Check Path**: `/api/health` or `/health`
- This endpoint doesn't require authentication

### 6. Deploy

Once configured:
1. Railway will automatically build from your Dockerfile
2. The entrypoint script will parse MySQL connection from Railway variables
3. Migrations will run automatically
4. Service will start on port 8000

## Troubleshooting

### Database Connection Issues

If database connection fails:
1. Verify MySQL service is in the same environment
2. Check that services are linked/connected
3. Verify `MYSQL_URL` or `MYSQLHOST` is available in environment variables
4. Check logs: `railway logs` or Railway dashboard

### Health Check Failing

- Ensure health check path is set to `/api/health` or `/health`
- These endpoints are public and don't require authentication

