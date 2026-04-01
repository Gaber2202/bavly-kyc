# Local setup: MySQL + PHP + Composer (macOS)

This project expects **MySQL 8.x**, **PHP 8.3+** (extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `bcmath` recommended), **Composer**, and **Node.js 20+**.

---

## 1) Install MySQL

### Option A — Homebrew (Intel / Apple Silicon)

```bash
brew install mysql
brew services start mysql
```

First run may compile heavy dependencies; on older macOS, **update Xcode Command Line Tools** if brew warns:

```text
sudo rm -rf /Library/Developer/CommandLineTools
sudo xcode-select --install
```

### Option B — MySQL Community installer

Download the **macOS DMG** from [MySQL Community Downloads](https://dev.mysql.com/downloads/mysql/).  
Complete the installer wizard and note the **root password** you set.

### Option C — GUI tools (quick dev)

Tools like **DBngin** or **MAMP** ship MySQL; point `.env` `DB_HOST` / `DB_PORT` to their socket or port.

---

## 2) Create database and app user

As MySQL **root**:

```bash
mysql -u root -p < scripts/mysql-init.sql
```

Edit `scripts/mysql-init.sql` if you want a different password, then use the same values in `.env` (`DB_USERNAME`, `DB_PASSWORD`).

---

## 3) Install PHP and Composer

```bash
brew install php composer
php -v    # should be 8.3+
composer -V
```

If `pdo_mysql` is missing:

```bash
php -m | grep pdo_mysql
# If empty, reinstall/link PHP from brew or enable extension in php.ini
```

---

## 4) Configure Laravel and run

```bash
cd /path/to/Bavly-KYC
cp .env.mysql.example .env
# Edit .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD if you changed them

bash scripts/run-local-mysql.sh
```

Or manually:

```bash
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install && npm run build
php artisan serve
```

---

## 5) Troubleshooting

| Issue | What to try |
|--------|-------------|
| `Connection refused` | Start MySQL: `brew services start mysql` or start from MySQL preferences. |
| `Access denied` | Re-run `mysql-init.sql` or fix `DB_USERNAME` / `DB_PASSWORD` in `.env`. |
| Homebrew build takes forever | Update Command Line Tools; or use MySQL DMG / DBngin instead of brew. |
| `SQLSTATE[HY000] [2002]` | Wrong `DB_HOST`; try `127.0.0.1` instead of `localhost` if socket issues. |

---

## 6) Default login after seed

- **Username:** `superadmin`  
- **Password:** `SEED_ADMIN_PASSWORD` from `.env`, or the default in `database/seeders/DatabaseSeeder.php` if unset.

Rotate these immediately for anything beyond local dev.
