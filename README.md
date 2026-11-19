# SuperLearn — Backend API

Laravel 13 backend for the SuperLearn ed-tech app. The mobile app lives in a
separate repo: **superlearn-app** (React Native / Expo).

## Stack

- PHP 8.4, Laravel 13
- Laravel Sanctum: token auth for the mobile app
- SQLite for local dev (switch to MySQL in `.env` for production)
- PHPUnit feature tests

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # or set DB_CONNECTION=mysql + DB_* in .env
php artisan migrate
php artisan serve                # http://127.0.0.1:8000
```

To reach the API from a phone on the same Wi-Fi, run
`php artisan serve --host 0.0.0.0 --port 8000` and use your PC's LAN IP in the app.

## API (v1)

All routes are prefixed with `/api/v1`. Send `Accept: application/json`.

| Method | Path             | Auth   | Body                                              |
|--------|------------------|--------|---------------------------------------------------|
| GET    | `/health`        | none   | n/a                                               |
| POST   | `/auth/register` | none   | `name`, `email`, `password`, `device_name?`        |
| POST   | `/auth/login`    | none   | `email`, `password`, `device_name?`                |
| GET    | `/auth/me`       | Bearer | n/a                                               |
| POST   | `/auth/logout`   | Bearer | n/a (revokes the current token)                   |

Register and login return `{ token, user }`. Send the token as
`Authorization: Bearer <token>`. Register and login are rate limited to 10 requests per minute.

## Tests

```bash
php artisan test
```

## Project layout

```
app/Http/Controllers/Api/   API controllers (AuthController)
routes/api.php              /api/v1 routes
tests/Feature/Api/          API feature tests
```
