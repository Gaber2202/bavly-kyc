# Bavly KYC — Hostinger VPS (Ubuntu) — step-by-step

Deploy **Laravel 12** from **GitHub** (`Gaber2202/bavly-kyc`, branch `main`) on a **Hostinger VPS** running **Ubuntu 22.04 or 24.04**.

**Shared vs VPS, security, rollback, and full checklist:** [DEPLOYMENT.md](DEPLOYMENT.md)

**Assumptions**

- You have the VPS **public IP**, **root** or a user with `sudo`.
- Your **domain** (e.g. `kyc.example.com`) has an **A record** pointing to that IP (Hostinger DNS or external DNS).
- You can **SSH** into the server (`ssh root@YOUR_IP` or `ssh ubuntu@YOUR_IP`).

---

## Step 1 — Connect and update the system

```bash
ssh root@YOUR_VPS_IP
# or: ssh ubuntu@YOUR_VPS_IP

sudo apt update && sudo apt upgrade -y
sudo apt install -y curl git unzip ufw
```

Optional firewall (SSH first so you are not locked out):

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
sudo ufw status
```

---

## Step 2 — Install PHP 8.3 + extensions (FPM)

Laravel 12 needs **PHP 8.3+**.

**Ubuntu 22.04** — use Ondřej Surý’s PPA:

```bash
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-xml php8.3-mbstring \
  php8.3-curl php8.3-zip php8.3-bcmath php8.3-intl php8.3-readline
php -v
```

**Ubuntu 24.04** — try `apt install php8.3-*` first; if packages are missing, use the same PPA as above.

---

## Step 3 — Install Composer

```bash
cd /tmp
curl -sS https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
composer --version
```

---

## Step 4 — Install Node.js 20+ (for Vite build)

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
node -v && npm -v
```

---

## Step 5 — Install Nginx and MySQL

```bash
sudo apt install -y nginx mysql-server
sudo systemctl enable --now nginx
sudo systemctl enable --now mysql
```

**Create database and user** (adjust names/passwords):

```bash
sudo mysql -e "CREATE DATABASE bavly_kyc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER 'bavly'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';"
sudo mysql -e "GRANT ALL PRIVILEGES ON bavly_kyc.* TO 'bavly'@'localhost'; FLUSH PRIVILEGES;"
```

On some images `sudo mysql` opens root without password; if login fails, use `sudo mysql_secure_installation` and set a root password first (Hostinger may document this).

---

## Step 6 — Deploy user and app directory

Using a dedicated user avoids running Git/Composer as root:

```bash
sudo adduser --disabled-password --gecos "" deploy
sudo mkdir -p /var/www/bavly-kyc
sudo chown deploy:deploy /var/www/bavly-kyc
```

**As `deploy`** (switch: `sudo su - deploy`):

```bash
cd /var/www/bavly-kyc
git clone https://github.com/Gaber2202/bavly-kyc.git .
# Private repo: use SSH deploy key or PAT; see DEPLOY_HOSTINGER.md
git checkout main
```

---

## Step 7 — Environment file

Still as `deploy` in `/var/www/bavly-kyc`:

```bash
cp .env.example .env
nano .env
```

Set at least:

| Variable | Example |
|----------|---------|
| `APP_NAME` | `Bavly KYC` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://kyc.yourdomain.com` |
| `APP_KEY` | leave empty; generate next step |
| `DB_DATABASE` | `bavly_kyc` |
| `DB_USERNAME` | `bavly` |
| `DB_PASSWORD` | your MySQL user password |
| `DB_HOST` | `127.0.0.1` |
| `TRUSTED_PROXIES` | `*` (or your proxy IPs) |
| `SESSION_SECURE_COOKIE` | `true` (with HTTPS) |
| `LOG_LEVEL` | `error` |

Generate key and install dependencies:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force
```

Optional: set `SEED_ADMIN_PASSWORD` in `.env` **before** `db:seed` for a known initial admin password, then remove or rotate.

Build assets:

```bash
npm ci
npm run build
```

**Permissions** (run with sudo from root or another admin session):

```bash
sudo chown -R deploy:www-data /var/www/bavly-kyc
sudo find /var/www/bavly-kyc -type d -exec chmod 755 {} \;
sudo find /var/www/bavly-kyc -type f -exec chmod 644 {} \;
sudo chmod -R 775 /var/www/bavly-kyc/storage /var/www/bavly-kyc/bootstrap/cache
sudo chmod 640 /var/www/bavly-kyc/.env
sudo chmod +x /var/www/bavly-kyc/artisan
```

Nginx/PHP-FPM run as `www-data`; `storage` and `bootstrap/cache` must stay group-writable for `www-data` (`775` and `deploy:www-data`).

Production caches:

```bash
cd /var/www/bavly-kyc
sudo -u deploy php artisan config:cache
sudo -u deploy php artisan route:cache
sudo -u deploy php artisan view:cache
```

---

## Step 8 — Nginx site configuration

Create `/etc/nginx/sites-available/bavly-kyc` (replace `kyc.yourdomain.com`):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name kyc.yourdomain.com;
    root /var/www/bavly-kyc/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

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

Enable site and test:

```bash
sudo ln -s /etc/nginx/sites-available/bavly-kyc /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx
```

**PHP-FPM pool:** if you hit permission errors, ensure `www-data` can read `/var/www/bavly-kyc`. Adjust `listen.owner` / `listen.group` in `/etc/php/8.3/fpm/pool.d/www.conf` only if needed (default is usually fine).

---

## Step 9 — HTTPS (Let’s Encrypt)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d kyc.yourdomain.com
```

Certbot will modify Nginx for SSL. Renewals are automatic via systemd timer.

---

## Step 10 — Laravel scheduler (cron)

The app does not require schedulers for core MVP unless you add scheduled tasks; if you use `schedule()` later, add:

```bash
sudo crontab -e -u deploy
```

Add:

```cron
* * * * * cd /var/www/bavly-kyc && php artisan schedule:run >> /dev/null 2>&1
```

---

## Step 11 — Queue worker (optional)

The project defaults to `QUEUE_CONNECTION=database`. For async jobs in production, use **Supervisor**:

```bash
sudo apt install -y supervisor
sudo nano /etc/supervisor/conf.d/bavly-kyc-worker.conf
```

Example:

```ini
[program:bavly-kyc-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/bavly-kyc/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deploy
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/bavly-kyc/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start bavly-kyc-worker:*
```

If you stay on `sync` queue driver, you can skip this.

---

## Step 12 — After each deploy

As `deploy`:

```bash
cd /var/www/bavly-kyc
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci && npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

---

## Checklist

- [ ] DNS **A record** → VPS IP
- [ ] `APP_DEBUG=false`, `APP_URL` matches HTTPS URL
- [ ] MySQL user/password work (`php artisan migrate` OK)
- [ ] First login: change **superadmin** / demo employee passwords
- [ ] `storage` and `bootstrap/cache` writable by PHP
- [ ] TLS active; `SESSION_SECURE_COOKIE=true`
- [ ] Hostinger **backups** or snapshot the VPS + DB dumps

---

## Related docs

- Shared hosting (no full VPS): [DEPLOY_HOSTINGER.md](DEPLOY_HOSTINGER.md)
- Security / production notes: [REFACTOR_SECURITY_PERFORMANCE.md](REFACTOR_SECURITY_PERFORMANCE.md)
