# Authentication Setup - MVP vs MVP 2

## ✅ Current Setup (MVP)

### Authentication Method
- **Username-based** - No email required
- **Login**: Username + Password only
- **Registration**: **DISABLED** (only Mazen & Maher can use the app)

### MVP Users (Auto-seeded)
- **Mazen**: username `Mazen`, password `password`
- **Maher**: username `Maher`, password `password`

### Login Example
```bash
POST /api/login
{
  "username": "Mazen",
  "password": "password"
}
```

### Registration Status
- ❌ **Registration is DISABLED** by default
- Only Mazen and Maher can log in
- Registration endpoint returns 403 error

## 🚀 MVP 2 Setup (Future)

### Enable Registration
Set in `.env`:
```env
ALLOW_REGISTRATION=true
```

### Registration (When Enabled)
```bash
POST /api/register
{
  "name": "John Doe",
  "username": "johndoe",
  "phone": null,              # Optional - not required
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Note:** Only username and password are required. Phone is optional.

## Database Schema

### Users Table
- `username` - **Required, Unique** (for authentication)
- `phone` - **Nullable, Optional** (not required)
- `password` - **Required** (hashed)

## Configuration

### config/app.php
```php
'allow_registration' => env('ALLOW_REGISTRATION', false),
```

### .env
```env
# MVP: Keep as false (only Mazen & Maher)
ALLOW_REGISTRATION=false

# MVP 2: Set to true (allow others to register)
ALLOW_REGISTRATION=true
```

## Summary

✅ **No email required** - Username-based authentication only
✅ **Only username + password** needed for authentication
✅ **Registration disabled** for MVP (only Mazen & Maher)
✅ **Easy to enable** for MVP 2 (set ALLOW_REGISTRATION=true)

