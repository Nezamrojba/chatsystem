# Chat Backend API

Laravel backend API for the chat application.

## Setup

1. Install dependencies: `composer install`
2. Copy `.env.example` to `.env`
3. Generate app key: `php artisan key:generate`
4. Run migrations: `php artisan migrate`
5. Seed database: `php artisan db:seed`
6. Start server: `php artisan serve`

## Environment Variables

See `.env.example` for required variables.

## Deployment

The backend is configured for Docker deployment. See `Dockerfile` and `docker-entrypoint.sh` for details.

