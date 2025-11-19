# Railway Settings Configuration Guide

## Critical: Disable Healthcheck

### Find This Section:
```
Healthcheck Path
Endpoint to be called before a deploy completes...

Healthcheck Path
/api/user  ← DELETE THIS
```

### Steps:
1. **Scroll down** to "Healthcheck Path" section
2. **Click on the field** that shows `/api/user`
3. **Delete everything** - make it completely empty
4. **Click "Update"** or "Save" button at the bottom

## Recommended Settings

### Source
- ✅ **Root Directory**: `.` (current - correct)
- ✅ **Branch**: `main` (current - correct)
- ✅ **Wait for CI**: Can be enabled if you use GitHub Actions

### Networking
- ✅ **Public Domain**: `chat-backend-api-production.up.railway.app` (auto-generated - keep it)
- ✅ **Private Domain**: `chat-backend-api.railway.internal` (for internal communication - keep it)

### Build
- ✅ **Builder**: `Dockerfile` (current - correct)
- ✅ **Dockerfile Path**: `Dockerfile` (current - correct)
- ✅ **Metal Build Environment**: Can enable for faster builds (optional)

### Deploy
- ✅ **Start Command**: Leave empty (Dockerfile CMD will be used)
- ✅ **Regions**: `US West` with `1` replica (current - correct)
- ✅ **Resource Limits**: 
  - CPU: 2 vCPU (or lower if needed)
  - Memory: 1 GB (or lower if needed)
- ❌ **Healthcheck Path**: **EMPTY** (clear `/api/user`)
- ✅ **Healthcheck Timeout**: Can leave at 300 or set to 0
- ✅ **Restart Policy**: `On Failure` (current - correct)
- ✅ **Max restart retries**: 10 (current - correct)

## Quick Fix Checklist

- [ ] Clear "Healthcheck Path" field (delete `/api/user`)
- [ ] Click "Update" button
- [ ] Wait for redeploy
- [ ] Deployment should succeed

## After Configuration

Once healthcheck is disabled:
1. Railway will deploy immediately
2. No waiting for healthcheck
3. Your app will start normally
4. Database will connect automatically
5. Everything will work! 🎉

