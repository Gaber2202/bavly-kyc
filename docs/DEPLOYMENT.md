# Bavly KYC — Complete deployment guide (Hostinger)

Production deployment for **Laravel 12 + MySQL** on **Hostinger** (shared hosting or VPS). Use this as the single source of truth; VPS-specific Nginx steps: [DEPLOY_HOSTINGER_VPS_UBUNTU.md](DEPLOY_HOSTINGER_VPS_UBUNTU.md). Shared/Git overview: [DEPLOY_HOSTINGER.md](DEPLOY_HOSTINGER.md).

---

## 1. Deployment checklist

### 1.1 PHP compatibility

| Item | Requirement |
|------|-------------|
| **PHP** | **8.3 or 8.4** (`composer.json`: `"php": "^8.3"`) |
| **Composer** | 2.x on server or deploy from CI with committed `composer.lock` |
| **Memory** | `composer install` may need `php -d memory_limit=512M /usr/local/bin/composer install` on small VPS |

### 1.2 Required PHP extensions

Install/enable (names vary slightly by OS):

- `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pcre`, `pdo`, `tokenizer`, `xml`
- **`pdo_mysql`** (MySQL)
- **`zip`** (Composer / packages)
- **`intl`** (recommended, locales)
- **`bcmath`** (recommended, decimals / money fields)

**Ubuntu example:**  
`php8.3-fpm php8.3-cli php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip php8.3-bcmath php8.3-intl`

**Verify:** `php -m | grep -E pdo_mysql|mbstring|zip|bcmath`

### 1.3 `.env` — production values

Copy from `.env.example` and set **before** `config:cache`:

| Variable | Production guidance |
|----------|---------------------|
| `APP_NAME` | Your product name |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` (never `true` on public internet) |
| `APP_KEY` | `php artisan key:generate` once; keep stable across deploys |
| `APP_URL` | Full base URL, **HTTPS**, no trailing slash: `https://kyc.example.com` |
| `TRUSTED_PROXIES` | `*` on Hostinger / behind CDN; or comma-separated IPs if you know them |
| `LOG_LEVEL` | `error` (or `warning`) |
| `DB_*` | From Hostinger MySQL wizard or VPS `mysql` user |
| `SESSION_DRIVER` | `database` (requires `sessions` table — included in default Laravel migrations) |
| `SESSION_SECURE_COOKIE` | `true` when site is **HTTPS-only** |
| `SESSION_DOMAIN` | Usually `null`; set only if you share cookies across subdomains |
| `CACHE_STORE` | `database` is fine for MVP (no Redis required) |
| `QUEUE_CONNECTION` | **`sync`** if no worker (recommended on **shared**). **`database`** + `queue:work` on **VPS** (optional; this app has no queued jobs by default) |
| `MAIL_*` | Set real mailer for password/ops mail if you enable it later (`log` is OK for internal-only) |

Reference SQL (optional, not a substitute for migrations): [database/mysql_schema.sql](database/mysql_schema.sql).

### 1.4 Database

- Create database + user in hPanel (**utf8mb4**).
- Prefer **`php artisan migrate --force`** (never edit migration history in production blindly).
- **Import SQL dump** only for disaster recovery or migrating from a snapshot; then ensure schema matches migrations.

### 1.5 Session / cache / mail

- **Sessions:** `database` — run migrations so `sessions` exists.
- **Cache:** `database` — migrations include `cache` table.
- **Mail:** default `.env.example` uses `log`; change when you need outbound email.

### 1.6 Storage and symlink

Public uploads use **`storage/app/public`** → must be visible at **`/storage/...`** via symlink.

```bash
php artisan storage:link
```

Run once per deployment path. If hosting disallows `symlink()`, copy strategy or nginx `alias` (advanced) — on most Hostinger **SSH** setups, `storage:link` works.

### 1.7 Permissions

| Path | Typical mode | Owner/group note |
|------|----------------|------------------|
| `storage/` | writable by web server | e.g. `775`, `www-data` or panel user |
| `bootstrap/cache/` | writable by web server | `775` |

**Never** make `.env` world-readable: `chmod 600` or `640`, owned by deploy user.

### 1.8 Queue strategy (no worker)

- Set **`QUEUE_CONNECTION=sync`** so jobs run inline (this codebase does not dispatch queued jobs today).
- If you later add queues on VPS, switch to `database`, run `php artisan queue:work` under **Supervisor** (see VPS doc).

### 1.9 Cron (scheduler)

This MVP does not require scheduled tasks. If you add `app/Console/Kernel` schedules or `routes/console.php` schedules later:

```cron
* * * * * cd /path/to/bavly-kyc && php artisan schedule:run >> /dev/null 2>&1
```

User for cron should own the project files (often `deploy` on VPS).

---

## 2. Shared hosting vs VPS (Hostinger)

### 2.1 Shared hosting

**Pros:** Managed PHP/MySQL, easy SSL, low ops.  
**Cons:** No guaranteed long-running **`queue:work`**; limited shell; document root quirks.

**Approach:**

1. **Document root** must be **`/path/to/bavly-kyc/public`** (not project root).
2. Deploy via **Git** (if available) or **upload** excluding `node_modules`, `vendor` (then `composer install` via SSH or Hostinger terminal if offered).
3. Run **`npm run build`** locally or on CI; upload **`public/build`** if Node is absent on server.
4. Use **`QUEUE_CONNECTION=sync`**.

See: [DEPLOY_HOSTINGER.md](DEPLOY_HOSTINGER.md).

### 2.2 VPS (Ubuntu + Nginx)

**Pros:** Full control, Supervisor, predictable **`composer`/`npm`**, proper **`public`** vhost.  
**Cons:** You secure and patch the OS.

**Approach:** Nginx `root` → `.../bavly-kyc/public`, PHP-FPM, MySQL local, Certbot SSL.

See: [DEPLOY_HOSTINGER_VPS_UBUNTU.md](DEPLOY_HOSTINGER_VPS_UBUNTU.md).

### 2.3 Point domain to `public`

- **Shared:** hPanel → domain → **Document root** → select or type `.../bavly-kyc/public`.
- **VPS:** Nginx `root /var/www/bavly-kyc/public;`

### 2.4 Import MySQL vs migrations

| Method | When |
|--------|------|
| **`php artisan migrate --force`** | Normal first deploy and updates (preferred). |
| **`mysql ... < backup.sql`** | Restore from backup; ensure Laravel migration table matches reality. |
| **Reference `mysql_schema.sql`** | Design reference / DBA review; **do not** rely on it alone if migrations differ. |

### 2.5 Safe migrations

```bash
php artisan migrate --force
```

- Run **after** backup in production.
- For risky releases: test on staging; use maintenance mode if needed:

```bash
php artisan down --secret="token"
# deploy…
php artisan migrate --force
php artisan up
```

---

## 3. Production optimization

Run **after** `.env` is correct (especially `APP_URL`):

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

**One-shot (Composer script):**

```bash
composer run deploy:optimize
```

**Artisan meta (optional):**

```bash
php artisan optimize
```

**Never** leave `APP_DEBUG=true` in production — error pages are user-safe when debug is off (see custom views under `resources/views/errors/`).

**Clear caches** when debugging config issues only:

```bash
php artisan optimize:clear
```

---

## 4. Security after deployment

| Topic | Action |
|-------|--------|
| **`.env`** | Not in Git; `chmod 640` or `600`; unique `APP_KEY`. |
| **HTTPS** | Enable SSL in panel; `APP_URL` must be `https://`; app forces **HTTPS scheme** when `APP_URL` uses `https`. |
| **Sessions/cookies** | `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true`, `http_only` session cookies (Laravel default). |
| **Directory listing** | `public/.htaccess` sets **`-Indexes`** inside `mod_negotiation` block (Apache). |
| **Logs** | `LOG_LEVEL=error`; monitor `storage/logs/laravel.log`; rotate via logrotate on VPS. |
| **Backups** | Hostinger backups + regular **`mysqldump`**; store off-server. |
| **Seeded passwords** | Change `superadmin` and demo employees immediately; remove demo users when going live if not needed. |

**TrustProxies** is configured in `bootstrap/app.php` from `TRUSTED_PROXIES` so `HTTPS` is detected behind Hostinger/load balancers.

---

## 5. Exact deployment steps (terminal)

**First-time (project root, SSH):**

```bash
git clone https://github.com/Gaber2202/bavly-kyc.git && cd bavly-kyc
git checkout main
cp .env.example .env
nano .env   # set APP_URL, DB_*, QUEUE_CONNECTION=sync on shared, etc.
composer install --no-dev --optimize-autoloader
php artisan key:generate --force
php artisan storage:link
php artisan migrate --force
# Optional first run only — rotate passwords after:
# php artisan db:seed --force
npm ci
npm run build
composer run deploy:optimize
```

**Ensure permissions** (adjust user/group to your host):

```bash
chmod -R u+rwX,go+rX storage bootstrap/cache
# chown -R deploy:www-data storage bootstrap/cache  # typical VPS
```

**Every release:**

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci && npm run build
composer run deploy:optimize
```

Or use **`bash scripts/deploy-production.sh --migrate`** (runs `composer install`, optional migrate flag, `npm ci && build`, `deploy:optimize`).

---

## 6. Hostinger-specific notes

- **Database host** in hPanel is sometimes **not** `127.0.0.1` — copy the exact hostname from the panel.
- **PHP version** per domain: choose **8.3+** in **PHP Configuration**.
- **`public`** as document root is mandatory.
- **`vendor/`** and **`public/build/`** are **not** in Git — always run **`composer install`** and **`npm run build`** (or upload build artifacts).
- **Private GitHub repo:** use **Deploy keys** (read-only SSH key on server).
- **OpenLiteSpeed / LiteSpeed:** if used instead of Apache, use Hostinger’s LiteSpeed rewrite rules for Laravel (similar to `try_files` / front controller).

---

## 7. Rollback checklist

1. Put app in maintenance: `php artisan down` (optional).
2. **Code:** `git checkout <previous-tag-or-commit>` or redeploy previous artifact.
3. **Dependencies:** `composer install --no-dev --optimize-autoloader`.
4. **Database:** restore **mysqldump** if a migration broken prod, or `php artisan migrate:rollback --step=1` only if safe and understood.
5. **Caches:** `php artisan optimize:clear` then re-run `composer run deploy:optimize` for known-good config.
6. **Up:** `php artisan up`.
7. Verify login, one KYC flow, admin report, export.

---

## 8. Post-deployment testing checklist

- [ ] `https://` loads without mixed-content warnings.
- [ ] Login (`superadmin` / known password).
- [ ] Create KYC record, edit, view list filters.
- [ ] Soft delete / restore if implemented in UI.
- [ ] Admin-only: user list, password reset, Excel export.
- [ ] Reports page (with permission).
- [ ] Force-password flow if admin reset password.
- [ ] 404 unknown URL shows friendly page (not debug trace).
- [ ] `storage:link` — file in `storage/app/public` reachable if you use public disk uploads later.

---

## 9. Common issues and fixes

| Symptom | Likely cause | Fix |
|---------|----------------|-----|
| **500** blank / generic page | Permissions, missing `.env`, missing `APP_KEY` | Check `storage/logs/laravel.log`; `chmod` storage; `key:generate` |
| **CSS/JS 404** | Vite not built | `npm ci && npm run build`; deploy `public/build` |
| **419 Page Expired** | Session/cookie domain, `APP_URL` mismatch, HTTP/HTTPS mix | Align `APP_URL` with browser URL; `SESSION_SECURE_COOKIE`; clear cookies |
| **DB connection refused** | Wrong `DB_HOST` / firewall | Use hPanel DB host; VPS `127.0.0.1` |
| **403 on storage files** | Missing `storage:link` | Nginx/Apache must serve `public/storage` |
| **Slow first request** | No OPCache / cold FPM | Enable OPcache on VPS; `config:cache` |
| **Composer memory** | Low PHP memory | `COMPOSER_MEMORY_LIMIT=-1` or `-d memory_limit=512M` |

---

## 10. Related files in this repo

- `.env.example` — production-oriented defaults and comments  
- `composer.json` → script `deploy:optimize`  
- `app/Providers/AppServiceProvider.php` — HTTPS URL forcing when `APP_URL` is `https`  
- `app/Http/Middleware/SecurityHeaders.php` — HSTS when connection is TLS  
- `resources/views/errors/*` — user-facing errors when `APP_DEBUG=false`  
- `public/.htaccess` — `Options -Indexes`  

---

**After each structural deploy change:** run `composer run deploy:optimize` and re-read §8.
