# Deploy LearnHost LMS on Ubuntu 24.04

This guide installs the **Laravel 11** + MySQL LMS on **Ubuntu 24.04** with **Nginx**, **PHP 8.3-FPM**, and **MySQL 8**.

Unlike Hostinger shared hosting, you run **Composer on the server** and deploy with Git (or rsync). See [HOSTINGER.md](HOSTINGER.md) if you still need the zip-only shared-hosting workflow.

## Requirements

- Ubuntu 24.04 LTS (VPS or cloud instance)
- A domain pointed at the server (for HTTPS), or use the server IP for LAN testing
- SSH access with sudo
- PHP 8.2+ (PHP 8.3 recommended on Ubuntu 24.04)

### PHP extensions

`mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl`, `zip`, `gd`, `intl`

---

## 1. System preparation

Log in as a user with sudo, then update packages and configure the firewall:

```bash
sudo apt update && sudo apt upgrade -y
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

Optional: create a dedicated deploy user (replace `deploy` with your preferred name):

```bash
sudo adduser deploy
sudo usermod -aG www-data deploy
```

---

## 2. Install Nginx, MySQL, PHP 8.3, Composer, and Node

```bash
sudo apt install -y nginx mysql-server git unzip curl

# PHP 8.3 (add PPA if php8.3 packages are not found)
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml \
  php8.3-curl php8.3-zip php8.3-bcmath php8.3-gd php8.3-intl

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js (for building frontend assets on the server)
sudo apt install -y nodejs npm
```

Verify PHP:

```bash
php -v
```

You should see PHP 8.3.x.

Secure MySQL (follow prompts):

```bash
sudo mysql_secure_installation
```

---

## 3. Create MySQL database and user

```bash
sudo mysql
```

In the MySQL shell:

```sql
CREATE DATABASE learnhost CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'learnhost'@'localhost' IDENTIFIED BY 'your_strong_password';
GRANT ALL PRIVILEGES ON learnhost.* TO 'learnhost'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 4. Deploy the application

Choose an install path (example: `/var/www/lms`):

```bash
sudo mkdir -p /var/www/lms
sudo chown $USER:www-data /var/www/lms
cd /var/www/lms
git clone https://github.com/td425/LMS.git .
git checkout cursor/lms-system-dcce   # or your release branch
```

Install dependencies and build assets:

```bash
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build
```

Configure environment:

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` (use `nano .env` or your editor):

```env
APP_NAME=LearnHost
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=learnhost
DB_USERNAME=learnhost
DB_PASSWORD=your_strong_password

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Run migrations and seed demo data (first install only):

```bash
php artisan migrate --seed --force
php artisan storage:link
chmod -R 775 storage bootstrap/cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Or use the helper script for deploys and updates:

```bash
bash scripts/deploy-ubuntu.sh          # update / redeploy
bash scripts/deploy-ubuntu.sh --seed   # first install with demo data
```

---

## 5. Configure Nginx

Create a site config (replace `your-domain.com` and paths as needed):

```bash
sudo nano /etc/nginx/sites-available/lms
```

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/lms/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    client_max_body_size 20M;

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site and reload Nginx:

```bash
sudo ln -s /etc/nginx/sites-available/lms /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

Ensure PHP-FPM is running:

```bash
sudo systemctl enable php8.3-fpm
sudo systemctl start php8.3-fpm
```

Set ownership so Nginx/PHP can write to `storage` and `bootstrap/cache`:

```bash
sudo chown -R www-data:www-data /var/www/lms/storage /var/www/lms/bootstrap/cache
sudo chmod -R 775 /var/www/lms/storage /var/www/lms/bootstrap/cache
```

---

## 6. HTTPS with Certbot

Skip this step for LAN-only testing; set `APP_URL=http://your-server-ip` instead.

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

Certbot will configure SSL and set up auto-renewal. After HTTPS is active, update `.env`:

```env
APP_URL=https://your-domain.com
```

Then refresh caches:

```bash
php artisan config:cache
```

---

## 7. Post-deploy checklist

1. Open `https://your-domain.com` and confirm the site loads
2. Log in with demo accounts (password: `password`):
   - `admin@lms.test` — admin
   - `instructor@lms.test` — instructor
   - `student@lms.test` — student
3. **Change all demo passwords immediately** on a live site
4. Visit `/admin/settings` and confirm site name and logo save correctly
5. Confirm the default logo appears at `public/images/logo-new.png`

---

## 8. Updating the application

After pulling new code:

```bash
cd /var/www/lms
git pull
bash scripts/deploy-ubuntu.sh
```

If frontend assets changed:

```bash
npm ci && npm run build
bash scripts/deploy-ubuntu.sh
```

---

## Troubleshooting

### 500 error on admin settings

```bash
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan view:clear
chmod -R 775 storage bootstrap/cache
php artisan storage:link
php artisan config:cache
```

### Logo not showing

- Check `public/images/logo-new.png` exists
- Run `php artisan storage:link`
- Clear view cache: `php artisan view:clear`

### Permission denied in `storage/` or `bootstrap/cache`

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 502 Bad Gateway

- Confirm PHP-FPM is running: `sudo systemctl status php8.3-fpm`
- Check the socket path in Nginx matches: `ls /run/php/php8.3-fpm.sock`

### Database connection failed

- Use `DB_HOST=127.0.0.1` on Ubuntu (not always required, but avoids socket issues)
- Verify credentials: `mysql -u learnhost -p learnhost`

---

## Features included

- Student registration / login (Laravel Breeze)
- Course catalog with search and level filter
- Enrollment, lesson viewer, progress tracking
- Instructor course and lesson management
- Admin site settings (logo + site name)
- Roles: `student`, `instructor`, `admin`
- Red theme with Mwasalat logo support

---

## Optional next steps

- **Redis** for cache and queues (`CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`)
- **Supervisor** to run `php artisan queue:work`
- **Automated backups** (`mysqldump` + archive `storage/`)
- Upgrade to **Laravel 12** when ready (PHP 8.2+ on this stack)
