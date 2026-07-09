# Deploy LearnHost LMS on Hostinger

This **Laravel 11** + MySQL LMS is designed for Hostinger shared / cloud hosting (PHP 8.2+).

> If you previously uploaded a Laravel 13 build, you will see:
> `Your Composer dependencies require a PHP version ">= 8.3.0"`.
> Re-upload this PHP 8.2-compatible release (or pull the latest branch), then run `composer install` again.

## Requirements

- **PHP 8.2+** (this app targets PHP 8.2 for Hostinger shared hosting)
- MySQL database
- PHP extensions: `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`
- `mod_rewrite` enabled (default on Hostinger)

### Confirm PHP version in hPanel

In **hPanel → Advanced → PHP Configuration** (or **Select PHP Version**), choose **PHP 8.2** or **8.3**.

CLI and website PHP should match. Check with:

```bash
php -v
```

## 1. Create MySQL database

In **hPanel → Databases → MySQL Databases**:

1. Create a database
2. Create a user and assign it to the database
3. Copy host (`localhost`), database name, username, and password

## 2. Upload the project

Upload the full project to your domain folder (for example `public_html`).

Recommended options:

- Git deploy / clone into the hosting account, or
- Upload a zip of the project (including `vendor/` and `public/build/` if you cannot run Composer/Node on the server)

If Composer is available via SSH:

```bash
composer install --no-dev --optimize-autoloader
```

## 3. Point the document root to `public`

In **hPanel → Domains → your domain → Document root**, set it to:

```text
/public_html/public
```

(or `.../your-folder/public`)

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
```

## 5. Storage permissions & link

```bash
chmod -R 775 storage bootstrap/cache
php artisan storage:link
```

## 6. Migrate and seed

```bash
php artisan migrate --force
php artisan db:seed --force
```

Demo accounts (password: `password`):

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

Frontend assets are already built into `public/build`. If you change CSS/JS locally:

```bash
npm install
npm run build
```

Then upload the updated `public/build` folder.

## 8. Local development (optional)

```bash
composer install
cp .env.example .env
php artisan key:generate
# For local SQLite quick start, set DB_CONNECTION=sqlite and touch database/database.sqlite
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

Open `http://127.0.0.1:8000`.

## Features included

- Student registration / login (Laravel Breeze)
- Course catalog with search and level filter
- Enrollment
- Lesson viewer + mark complete
- Progress tracking on dashboard
- Instructor course & lesson management
- Roles: `student`, `instructor`, `admin`
