# LearnHost LMS

A Laravel 11 + MySQL Learning Management System ready for **Hostinger** web hosting (PHP 8.2+).

## Features

- Course catalog with search and level filters
- Student registration, login, enrollment, and lesson progress
- Instructor tools to create/edit courses and lessons
- Roles: student, instructor, admin
- Blade UI with Laravel Breeze auth

## Quick start (local)

```bash
composer install
cp .env.example .env
php artisan key:generate
```

For a quick local SQLite setup, set in `.env`:

```env
APP_NAME=LearnHost
DB_CONNECTION=sqlite
# comment out DB_HOST / DB_DATABASE / DB_USERNAME / DB_PASSWORD
```

Then:

```bash
touch database/database.sqlite
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

Demo logins (password: `password`):

- `admin@lms.test`
- `instructor@lms.test`
- `student@lms.test`

## Hostinger deployment

See **[HOSTINGER.md](HOSTINGER.md)** for MySQL setup, document root (`public`), `.env`, migrations, and production cache steps.
