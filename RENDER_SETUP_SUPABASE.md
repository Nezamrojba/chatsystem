# Render Setup with Supabase Database

## 🎯 Quick Setup Guide

### Step 1: Get Supabase Connection Details

From your Supabase dashboard:

1. **Go to Settings → Database**
2. **Find "Connection string"** or look for:
   - **Host:** `db.pbgqqxbajlqiaaniazif.supabase.co` (your project ref)
   - **Port:** `5432` (or `6543` for connection pooling)
   - **Database:** `postgres`
   - **Username:** `postgres`
   - **Password:** `Mazen@99999` ✅ (you have this)

### Connection String Format:
```
postgresql://postgres:Mazen@99999@db.pbgqqxbajlqiaaniazif.supabase.co:5432/postgres
```

**OR use Connection Pooling (Recommended):**
- **Port:** `6543` (instead of 5432)
- Better for serverless/hosted apps
- Handles connection limits better

---

## Step 2: Set Up Render

### 2.1 Sign Up for Render
1. Go to [render.com](https://render.com)
2. Click "Get Started for Free"
3. Sign up with GitHub
4. Authorize Render to access your GitHub

### 2.2 Create Web Service
1. In Render dashboard, click **"New +"** → **"Web Service"**
2. **Connect GitHub:**
   - Click "Connect account" if not connected
   - Select repository: `Nezamrojba/chatsystem`
   - Click "Connect"

3. **Configure Service:**
   - **Name:** `chat-backend-api`
   - **Environment:** Select **"Docker"**
   - **Region:** Choose closest to you (e.g., `Oregon (US West)`)
   - **Branch:** `main`
   - **Root Directory:** `backend` (if your Dockerfile is in backend folder)
   - **Dockerfile Path:** `Dockerfile` (or `backend/Dockerfile`)
   - **Docker Context:** `backend` (if Dockerfile is in backend folder)

### 2.3 Add Environment Variables

Click **"Advanced"** → **"Add Environment Variable"** and add:

#### Required Variables:
```
APP_NAME=Mazen Maher Chat
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_URL=https://chat-backend-api.onrender.com
```

**Generate APP_KEY:**
```bash
cd backend
php artisan key:generate --show
```
Copy the output and paste as `APP_KEY` value.

#### Database Variables (Supabase):
```
DB_CONNECTION=pgsql
DB_HOST=db.pbgqqxbajlqiaaniazif.supabase.co
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=Mazen@99999
```

**Note:** Using port `6543` for connection pooling (recommended for hosted apps)

#### Other Variables:
```
BROADCAST_CONNECTION=null
FRONTEND_URL=https://your-frontend-url.com
LOG_CHANNEL=stack
LOG_LEVEL=error
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

### 2.4 Deploy
1. Click **"Create Web Service"**
2. Render will:
   - Build your Docker image
   - Deploy your service
   - Show build logs
3. Wait ~5-10 minutes for first deployment

### 2.5 Get Your Service URL
After deployment, you'll get a URL like:
```
https://chat-backend-api.onrender.com
```

---

## Step 3: Verify Connection

### 3.1 Check Deployment Logs
In Render dashboard → Your service → **"Logs"** tab:
- Look for: "Database is up!"
- Look for: "Running migrations..."
- Look for: "Running database seeders..."

### 3.2 Test API Endpoints
```bash
# Health check
curl https://chat-backend-api.onrender.com/

# Should return:
# {"status":"ok","service":"Mazen Maher Chat API","database":"connected"}
```

---

## Step 4: Update Frontend

Update your frontend `.env` or environment variables:

```env
VITE_API_URL=https://chat-backend-api.onrender.com
```

---

## 🔒 Security Notes

### Supabase Network Restrictions (Optional but Recommended)

1. **In Supabase Dashboard:**
   - Go to **Settings → Database → Network Restrictions**
   - Click **"Add restriction"**
   - Add Render's IP ranges (or allow all for now)

2. **For Production:**
   - Restrict to Render's IP ranges only
   - Use connection pooling (port 6543)
   - Enable SSL enforcement

### SSL Connection (Recommended)

Supabase requires SSL. Your Laravel app should handle this automatically, but verify in `config/database.php`:

```php
'pgsql' => [
    // ...
    'options' => [
        PDO::MYSQL_ATTR_SSL_CA => env('DB_SSL_CA'),
    ],
],
```

For Supabase, you might need to add SSL mode:
```php
'options' => [
    PDO::ATTR_EMULATE_PREPARES => true,
],
```

Or in connection string:
```
DB_URL=postgresql://postgres:Mazen@99999@db.pbgqqxbajlqiaaniazif.supabase.co:6543/postgres?sslmode=require
```

---

## 🐛 Troubleshooting

### Connection Timeout
- Check Supabase **Network Restrictions** - make sure Render IPs are allowed
- Try port `5432` instead of `6543` (direct connection)
- Verify password is correct (no extra spaces)

### Migration Errors
- Ensure `DB_CONNECTION=pgsql` is set
- Check database exists in Supabase
- Verify user has proper permissions

### Build Errors
- Check Dockerfile path is correct
- Verify root directory matches your repo structure
- Check build logs in Render dashboard

---

## 📊 Connection Details Summary

```
Host: db.pbgqqxbajlqiaaniazif.supabase.co
Port: 6543 (pooling) or 5432 (direct)
Database: postgres
Username: postgres
Password: Mazen@99999
SSL: Required
```

---

## ✅ Checklist

- [ ] Sign up for Render
- [ ] Create Web Service
- [ ] Connect GitHub repo
- [ ] Configure Docker settings
- [ ] Add all environment variables
- [ ] Generate APP_KEY
- [ ] Deploy service
- [ ] Test health endpoint
- [ ] Verify database connection
- [ ] Run migrations (automatic)
- [ ] Update frontend API URL

---

## 🎉 You're Done!

After deployment, your Laravel backend will be:
- ✅ Hosted on Render (free)
- ✅ Connected to Supabase PostgreSQL (free)
- ✅ Auto-deploying from GitHub
- ✅ Running migrations automatically
- ✅ Ready for your frontend to connect!

**Total Cost: $0/month** 🎉

