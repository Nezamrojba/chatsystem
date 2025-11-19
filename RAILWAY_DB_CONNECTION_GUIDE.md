# Railway Database Connection Guide

## 🎯 Two Ways to Connect Database

### Method 1: Link Services (Recommended - Automatic)

When you link services, Railway automatically injects MySQL variables into your web service.

#### Steps:

1. **Go to Your Web Service**
   - Railway Dashboard → Your Project → **chat-backend-api** (Web Service)

2. **Open Settings Tab**
   - Click on **"Settings"** tab

3. **Find Service Connections**
   - Scroll down to **"Service Connections"** or **"Dependencies"** section
   - Look for **"Connected Services"** or **"Add Service"** button

4. **Link MySQL Service**
   - Click **"Add Service"** or **"Connect Service"**
   - Select your **MySQL service** from the list
   - Click **"Connect"** or **"Link"**

5. **Verify Variables (Optional)**
   - Go to **"Variables"** tab in your Web Service
   - You should now see these automatically injected:
     - `MYSQLHOST`
     - `MYSQLPORT`
     - `MYSQLDATABASE`
     - `MYSQLUSER`
     - `MYSQLPASSWORD`
     - `MYSQL_URL`

**✅ After linking, your `docker-entrypoint.sh` will automatically detect and use these variables!**

---

### Method 2: Manual Configuration (If Linking Doesn't Work)

If service linking doesn't work, you can manually set database variables.

#### Steps:

1. **Go to Your Web Service**
   - Railway Dashboard → Your Project → **chat-backend-api** (Web Service)

2. **Open Variables Tab**
   - Click on **"Variables"** tab (NOT Shared Variables)

3. **Add Database Variables**

   Click **"New Variable"** and add these one by one:

   ```
   Variable Name: DB_CONNECTION
   Variable Value: mysql
   ```

   ```
   Variable Name: DB_HOST
   Variable Value: ${{MySQL.RAILWAY_PRIVATE_DOMAIN}}
   ```
   OR use the actual host from your MySQL service variables (e.g., `mysql.railway.internal`)

   ```
   Variable Name: DB_PORT
   Variable Value: 3306
   ```

   ```
   Variable Name: DB_DATABASE
   Variable Value: ${{MySQL.MYSQLDATABASE}}
   ```
   OR use the actual database name (e.g., `railway`)

   ```
   Variable Name: DB_USERNAME
   Variable Value: ${{MySQL.MYSQLUSER}}
   ```
   OR use the actual username (e.g., `root`)

   ```
   Variable Name: DB_PASSWORD
   Variable Value: ${{MySQL.MYSQL_ROOT_PASSWORD}}
   ```
   OR use the actual password from your MySQL service

#### Using ${{REF}} Syntax:

Railway allows you to reference variables from other services using `${{ServiceName.VARIABLE_NAME}}`:

- `${{MySQL.MYSQLHOST}}` - References MySQL service's MYSQLHOST
- `${{MySQL.MYSQLDATABASE}}` - References MySQL service's MYSQLDATABASE
- etc.

**Note**: Replace `MySQL` with your actual MySQL service name if different.

---

## 🔍 How to Find Your MySQL Service Variables

1. **Go to MySQL Service**
   - Railway Dashboard → Your Project → **MySQL Service** (the database service)

2. **Open Variables Tab**
   - Click on **"Variables"** tab
   - You'll see all the MySQL connection variables

3. **Copy the Values You Need**
   - `MYSQLHOST` or `RAILWAY_PRIVATE_DOMAIN` → Use for `DB_HOST`
   - `MYSQLDATABASE` → Use for `DB_DATABASE`
   - `MYSQLUSER` → Use for `DB_USERNAME`
   - `MYSQL_ROOT_PASSWORD` or `MYSQLPASSWORD` → Use for `DB_PASSWORD`

---

## ✅ Verification

After setting up (either method), redeploy your service:

1. **Trigger Redeploy**
   - Railway will automatically redeploy when you change variables
   - OR go to **"Deployments"** tab → Click **"Redeploy"**

2. **Check Logs**
   - Go to **"Deploy Logs"** or **"Logs"** tab
   - You should see:
     ```
     Detected Railway MySQL individual variables (MYSQLHOST)...
     MySQL connection configured from Railway variables: ...
     Database is up!
     Running migrations...
     ```

3. **Test Health Endpoint**
   - Visit: `https://chat-backend-api-production.up.railway.app/api/health`
   - Should return JSON with `"database": "connected"`

---

## 🚨 Troubleshooting

### If MySQL variables still not detected:

1. **Check Service Names**
   - Make sure both services are in the **same environment** (production)
   - Service names must match when using `${{ServiceName.VAR}}`

2. **Check Variable Names**
   - Railway MySQL service uses: `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, etc.
   - Not `MYSQL_HOST`, `MYSQL_PORT` (those are alternatives)

3. **Manual Override**
   - If auto-detection fails, manually set all `DB_*` variables in your Web Service Variables tab

4. **Check Logs**
   - Look for: `"No Railway MySQL variables found"`
   - This means variables aren't being injected

---

## 📝 Current Setup

Based on your setup, you have:
- ✅ `BROADCAST_CONNECTION` in Shared Variables (good!)
- ❌ MySQL service not linked (needs to be done)

**Recommended Action**: Use **Method 1 (Link Services)** - it's the easiest and most reliable!

