# Deploy LearnHost LMS on Hostinger

This **Laravel 11** + MySQL LMS is designed for Hostinger shared / cloud hosting (PHP 8.2+).

Laravel is used for **security, scalability, and maintainability** (auth, CSRF, validation, caching, migrations, tests). You do **not** need to run Composer on Hostinger if you upload a pre-built package (see below).

> If you previously uploaded a Laravel 13 build, you will see:
> `Your Composer dependencies require a PHP version ">= 8.3.0"`.
> Re-upload this PHP 8.2-compatible release instead.

## Requirements

- **PHP 8.2+** (Hostinger shared hosting)
- MySQL database
- PHP extensions: `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`
- `mod_rewrite` enabled (default on Hostinger)

### Confirm PHP version in hPanel

In **hPanel → Advanced → PHP Configuration**, choose **PHP 8.2** or **8.3**.

```bash
php -v
```

## Recommended: deploy without Composer on Hostinger

Build the release **on your computer** (or in CI), then upload the zip to Hostinger.

### Step A — Build locally

```bash
composer install --no-dev --optimize-autoloader
```

Or run the helper script:

```bash
bash scripts/build-hostinger-zip.sh
```

This creates `learnhost-hostinger.zip` with everything Hostinger needs, including:

- `vendor/` (PHP dependencies — **no Composer on server**)
- `public/css/app.css` (pre-built styles — **no Node/npm**)
- `public/images/logo-new.png` (default Mwasalat logo)

### Step B — Upload to Hostinger

1. Upload `learnhost-hostinger.zip` to your domain folder (e.g. `public_html`)
2. Extract the zip
3. Set document root to `public/` (see step 3 below)

### Step C — Configure on server (SSH / Hostinger terminal)

```bash
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
chmod -R 775 storage bootstrap/cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**You only run `php artisan` commands on Hostinger — not `composer install`.**

## 1. Create MySQL database

In **hPanel → Databases → MySQL Databases**:

1. Create a database
2. Create a user and assign it to the database
3. Copy host (`localhost`), database name, username, and password

## 2. Upload the project

Upload the full project (or extracted zip) to your domain folder.

The upload **must include**:

- `vendor/`
- `public/css/app.css`
- `public/images/logo-new.png`

## 3. Point the document root to `public`

In **hPanel → Domains → your domain → Document root**, set it to:

```text
/public_html/public
```

If you cannot change the document root, keep the root `.htaccess` that forwards requests into `public/`.

## 4. Configure `.env`

```bash
cp .env.example .env
php artisan key:generate
```

Set at least:

```env
APP_NAME=LearnHost
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
SESSION_SECURE_COOKIE=false
```

Set `APP_URL` to your exact site URL. If login shows **Page Expired**, run `php artisan config:clear` and verify `APP_URL` and session settings above.

## 5. Storage permissions & link

```bash
chmod -R 775 storage bootstrap/cache
php artisan storage:link
```

Make sure these exist after upload:

- `public/images/logo-new.png` (default site logo)
- `public/storage` symlink (for admin-uploaded logos)

## 6. Migrate and seed

```bash
php artisan migrate --force
php artisan db:seed --force
```

Admins can change the logo and site name at `/admin/settings` after login.

If saving site settings returns a **500 error**, run:

```bash
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan view:clear
chmod -R 775 storage bootstrap/cache
php artisan storage:link
```

If you see **`BreezeServiceProvider` not found**, clear stale bootstrap cache:

```bash
php artisan optimize:clear
rm -f bootstrap/cache/*.php
php artisan package:discover
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Demo accounts

Password for all: `password`

| Role | Email |
|------|-------|
| Admin | `admin@lms.test` |
| Instructor | `instructor@lms.test` |
| Student | `student@lms.test` |

Change these passwords immediately on a live site.

## 7. Optimize for production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 8. Local development (optional)

```bash
composer install
cp .env.example .env
php artisan key:generate
# For SQLite: set DB_CONNECTION=sqlite and DB_DATABASE to database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Open `http://127.0.0.1:8000`.

## Features included

- Student registration / login (Laravel Breeze)
- Course catalog with search and level filter
- Enrollment, lesson viewer, progress tracking
- Instructor course & lesson management
- Admin site settings (logo + site name)
- Roles: `student`, `instructor`, `admin`
- Red theme with Mwasalat logo support
