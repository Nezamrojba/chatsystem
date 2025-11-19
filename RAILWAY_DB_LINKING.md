# How to Link MySQL Database in Railway

## Step-by-Step Guide

### 1. Ensure Both Services Exist

You need **two services** in the same Railway environment:
- **MySQL Database** service (already created)
- **Web Service** (your Laravel API)

### 2. Link Database to Web Service

#### Method 1: Service Connections (Recommended)

1. **Go to your Web Service**
   - Click on your web service in Railway dashboard
   - Go to **Settings** tab

2. **Find "Service Connections" or "Dependencies"**
   - Look for a section called:
     - "Service Connections"
     - "Dependencies" 
     - "Connected Services"
     - "Add Service"

3. **Link MySQL Service**
   - Click **"Add Service"** or **"Connect Service"**
   - Select your **MySQL service** from the list
   - Click **"Connect"** or **"Link"**

4. **Verify Connection**
   - After linking, Railway automatically provides MySQL variables:
     - `MYSQL_URL`
     - `MYSQLHOST`
     - `MYSQLPORT`
     - `MYSQLDATABASE`
     - `MYSQLUSER`
     - `MYSQLPASSWORD`

#### Method 2: Variables Tab (Alternative)

1. **Go to Web Service → Variables**
   - Click on your web service
   - Go to **Variables** tab

2. **Check if MySQL Variables are Present**
   - Look for variables starting with `MYSQL_`
   - If they're there, the services are already linked
   - If not, use Method 1 to link them

### 3. Verify Linking

After linking, check your Web Service environment variables. You should see:

```
MYSQL_URL=mysql://user:password@host:port/database
MYSQLHOST=your-mysql-host
MYSQLPORT=3306
MYSQLDATABASE=railway
MYSQLUSER=root
MYSQLPASSWORD=your-password
```

### 4. How It Works

When services are linked in Railway:
- Railway **automatically injects** MySQL connection variables
- Your `docker-entrypoint.sh` script **automatically detects** and uses them
- No manual configuration needed!

### 5. Troubleshooting

**If MySQL variables are missing:**

1. **Check Service Environment**
   - Both services must be in the **same environment** (e.g., "production")
   - MySQL service and Web service should be in the same project

2. **Re-link Services**
   - Unlink and re-link the MySQL service
   - Sometimes Railway needs a refresh

3. **Check Service Names**
   - Make sure you're linking the correct MySQL service
   - Verify the MySQL service is running

4. **Redeploy**
   - After linking, trigger a redeploy
   - Railway will inject the variables on next deployment

### 6. Manual Configuration (If Linking Doesn't Work)

If automatic linking doesn't work, you can manually set these in Web Service → Variables:

```env
DB_CONNECTION=mysql
DB_HOST=<from MySQL service variables: MYSQLHOST>
DB_PORT=3306
DB_DATABASE=<from MySQL service variables: MYSQLDATABASE>
DB_USERNAME=<from MySQL service variables: MYSQLUSER>
DB_PASSWORD=<from MySQL service variables: MYSQLPASSWORD>
```

But **automatic linking is preferred** - it's easier and Railway manages it for you.

## Quick Checklist

- [ ] MySQL service exists in Railway
- [ ] Web service exists in Railway
- [ ] Both services are in the same environment
- [ ] MySQL service is linked to Web service
- [ ] MySQL variables appear in Web service variables
- [ ] Redeploy web service after linking

## After Linking

Once linked, your app will:
1. Automatically detect MySQL connection from Railway variables
2. Connect to the database on startup
3. Run migrations automatically
4. Start serving requests

No additional configuration needed! 🎉

