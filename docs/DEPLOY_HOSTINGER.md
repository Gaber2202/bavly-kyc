# Deploy Bavly KYC on Hostinger from GitHub

Target repo: `https://github.com/Gaber2202/bavly-kyc` (branch `main`).

This app is **Laravel 12**: PHP **8.3+**, **Composer**, **MySQL**, and a **Vite** build for assets. On Hostinger, use **SSH + Git** when possible; the drag-and-drop Git panel varies by plan.

**Master checklist (PHP extensions, `.env`, queues, rollback, testing):** [DEPLOYMENT.md](DEPLOYMENT.md)

---

## 1. Before you start

| Requirement | Notes |
|-------------|--------|
| **PHP 8.3+** | hPanel → *Advanced* → **PHP Configuration** (or *Select PHP Version*) for the domain. |
| **MySQL** | hPanel → **Databases** → **MySQL Database Wizard** — create DB + user; note host (often `127.0.0.1` or a socket hostname shown in the panel). |
| **SSL** | Enable **Free SSL** (Let’s Encrypt) for your domain. |
| **SSH access** | Prefer a plan with **SSH**; deployment is much simpler. |

---

## 2. Point the domain at Laravel’s `public` folder

Laravel’s web root must be the `public` directory **not** the repository root.

1. hPanel → **Domains** → your domain → **Manage** (or *Hosting* settings).
2. Set **Document root** (or *Website root*) to the `public` folder inside the cloned project, for example:
   - If the app lives at `~/domains/yourdomain.com/bavly-kyc`, set document root to  
     `.../bavly-kyc/public`
3. Save and wait a minute for the change to apply.

If the panel only allows `public_html`, either:

- Clone the repo **outside** `public_html` and set document root to `.../public`, **or**
- Use Hostinger’s doc for “Laravel document root” for your exact product (shared vs VPS).

---

## 3. Get the code from Git

### Option A — SSH + clone (recommended)

```bash
ssh u123456789@your-server-host.hostinger.com
cd ~/domains/yourdomain.com   # or the path Hostinger documents for your account
git clone git@github.com:Gaber2202/bavly-kyc.git
cd bavly-kyc
git checkout main
```

**Private repo:** add an SSH **Deploy key** (read-only):

1. On the server: `ssh-keygen -t ed25519 -f ~/.ssh/bavly_kyc_deploy -N ""`
2. Show public key: `cat ~/.ssh/bavly_kyc_deploy.pub`
3. GitHub repo → **Settings** → **Deploy keys** → add key, allow read.
4. Use SSH config or clone URL that uses this key, e.g. in `~/.ssh/config`:

```sshconfig
Host github.com-bavly
  HostName github.com
  User git
  IdentityFile ~/.ssh/bavly_kyc_deploy
  IdentitiesOnly yes
```

Then:

```bash
git clone git@github.com-bavly:Gaber2202/bavly-kyc.git
```

### Option B — hPanel **Git** feature

If your plan shows **Git** under the website:

1. Connect the repository and branch `main`.
2. After the first clone, set **document root** to `…/bavly-kyc/public` (see §2).
3. Run the commands in §5 over **SSH** after each pull (or automate with a deploy script + cron if your plan allows).

---

## 4. Create `.env` on the server

Never commit `.env`. On the server:

```bash
cd ~/path/to/bavly-kyc
cp .env.example .env
nano .env   # or use File Manager
```

Set at least:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-domain.com` (no trailing slash)
- `APP_KEY=` — run `php artisan key:generate` once (§5)
- `DB_*` from Hostinger MySQL (database name, user, password; host/port as shown in hPanel)
- `TRUSTED_PROXIES=*` (already in `.env.example`; fine behind Hostinger / CDN)
- With HTTPS: `SESSION_SECURE_COOKIE=true` (already suggested in `.env.example`)

Optional: strong `SEED_ADMIN_PASSWORD` **before** first seed.

---

## 5. Install dependencies and deploy (SSH)

From the project root (`bavly-kyc`, same level as `artisan`):

```bash
# PHP dependencies (vendor/ is not in Git)
composer install --no-dev --optimize-autoloader

# App key (only if .env has empty APP_KEY)
php artisan key:generate --force

# Database
php artisan migrate --force

# First-time only: seed admin demo users (rotate passwords after)
php artisan db:seed --force

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Frontend build — use Hostinger Node if available:
node -v && npm ci && npm run build
```

If **`npm` is not available** on the server, build **on your Mac**, then upload the folder **`public/build`** to the server at the same path (same contents as after `npm run build`). Re-upload after UI changes.

Fix permissions (adjust user/group if Hostinger docs say so):

```bash
chmod -R ug+rwx storage bootstrap/cache
# If needed:
# find storage bootstrap/cache -type d -exec chmod 775 {} \;
# find storage bootstrap/cache -type f -exec chmod 664 {} \;
```

---

## 6. After every `git pull`

```bash
cd ~/path/to/bavly-kyc
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm ci && npm run build   # if you build on server; else upload public/build
```

---

## 7. Login after deploy

- **Username:** `superadmin` (from seeder)
- **Password:** `SEED_ADMIN_PASSWORD` if you set it before `db:seed`, otherwise the default in `database/seeders/DatabaseSeeder.php` — **change immediately** in production.

---

## 8. Troubleshooting

| Problem | What to check |
|--------|----------------|
| **500 error** | `storage/logs/laravel.log`, `APP_DEBUG` (temporarily `true` only on a staging copy to debug), permissions on `storage` / `bootstrap/cache`. |
| **CSS/JS missing** | Run `npm run build` or copy `public/build`; clear browser cache. |
| **Login / HTTPS** | `APP_URL` uses `https://`, SSL active, `SESSION_SECURE_COOKIE=true`. |
| **Database** | `DB_HOST` in hPanel (sometimes not `127.0.0.1`), correct DB name/user/password. |
| **419 Page Expired** | `APP_URL` must match the URL you use; session domain/cookie settings. |

---

## 9. VPS vs shared hosting

On a **VPS**, you can use Nginx/Apache templates, Supervisor for queues, and Redis later. This guide assumes **shared / managed PHP** where `database` sessions, cache, and queue are enough for the MVP.

---

## Reference

- Project overview: [README.md](../README.md)
- MySQL schema notes: [database/DESIGN.md](database/DESIGN.md)
