# LearnHost LMS

A **Laravel 11** + MySQL Learning Management System for **Hostinger** web hosting (PHP 8.2+).

Laravel provides built-in security (CSRF, auth, validation), caching, migrations, and a structure that scales better than plain PHP as your user base grows.

## Features

- Course catalog with search and level filters
- Student registration, login, enrollment, and lesson progress
- Instructor tools to create/edit courses and lessons
- Admin site settings (logo + site name) at `/admin/settings`
- Roles: student, instructor, admin
- Red theme with Mwasalat logo (`public/images/logo-new.png`)

## Quick start (local — requires Composer on your PC)

```bash
composer install
cp .env.example .env
php artisan key:generate
```

For a quick local SQLite setup, set in `.env`:

```env
APP_NAME=LearnHost
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
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

## Hostinger deployment (no Composer on server)

1. **Build locally** on your computer:

   ```bash
   composer install --no-dev --optimize-autoloader
   npm install && npm run build
   ```

   Or use: `bash scripts/build-hostinger-zip.sh`

2. **Upload** the zip (includes `vendor/` and `public/build/`) to Hostinger

3. **On server**, run only:

   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --seed --force
   php artisan storage:link
   ```

Full steps: **[HOSTINGER.md](HOSTINGER.md)**
