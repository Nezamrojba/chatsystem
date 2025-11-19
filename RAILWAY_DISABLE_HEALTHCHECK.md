# Disable Healthcheck in Railway

## Quick Fix

The healthcheck is configured in Railway's dashboard, not in code. To disable it:

### Steps:

1. **Go to Railway Dashboard**
   - Navigate to your service
   - Click on **Settings**

2. **Find Healthcheck Section**
   - Look for "Healthcheck" or "Health Check" settings
   - Find "Health Check Path" field

3. **Disable Healthcheck**
   - **Option 1**: Clear the "Health Check Path" field (leave it empty)
   - **Option 2**: If there's a toggle, turn it OFF
   - **Option 3**: Set timeout to 0 or disable the feature

4. **Save Changes**
   - Click "Save" or "Update"
   - Railway will redeploy without healthcheck

### Alternative: Use a Working Healthcheck Path

If you want to keep healthchecks enabled, change the path to:
- `/up` (Laravel's built-in health endpoint)
- `/api/health` (Our custom endpoint)
- `/health` (Web health endpoint)

All of these are public and don't require authentication.

### Why It's Failing

The current healthcheck path `/api/user` requires authentication, so Railway can't access it and marks the deployment as failed.

