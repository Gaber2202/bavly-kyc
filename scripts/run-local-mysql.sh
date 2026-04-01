#!/usr/bin/env bash
# Bavly KYC — local dev with MySQL (requires: MySQL server running, PHP 8.3+, Composer, Node)
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

export PATH="/opt/homebrew/bin:/usr/local/bin:$PATH"

for bin in php composer npm mysql; do
  command -v "$bin" >/dev/null 2>&1 || { echo "Missing: $bin"; echo "Install MySQL Server, PHP 8.3+, Composer, and Node.js."; exit 1; }
done

if [[ ! -f .env ]]; then
  cp .env.mysql.example .env
  echo "Created .env from .env.mysql.example — set DB_PASSWORD if you changed it in mysql-init.sql"
fi

# Load DB_* from .env for connectivity check (minimal parse)
set -a
# shellcheck disable=SC1091
source <(grep -E '^(DB_|APP_KEY)=' .env | sed 's/^/export /')
set +a

if [[ -z "${APP_KEY:-}" ]]; then
  echo "Generating APP_KEY..."
  composer install --no-interaction
  php artisan key:generate --force
fi

echo "Checking MySQL connection..."
php -r "
\$pdo = new PDO(
    'mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306') . ';dbname=' . getenv('DB_DATABASE'),
    getenv('DB_USERNAME'),
    getenv('DB_PASSWORD')
);
echo 'OK connected to ' . getenv('DB_DATABASE') . PHP_EOL;
" || {
  echo ""
  echo "Connection failed. Ensure:"
  echo "  1) MySQL is running (brew services start mysql, or start MySQL from System Settings)."
  echo "  2) Database and user exist: mysql -u root -p < scripts/mysql-init.sql"
  echo "  3) .env DB_* matches mysql-init.sql"
  exit 1
}

composer install --no-interaction
php artisan migrate --force
php artisan db:seed --force

npm install --no-fund --no-audit
npm run build

echo ""
echo "http://127.0.0.1:8000 — login: superadmin (password in DatabaseSeeder or SEED_ADMIN_PASSWORD)"
php artisan serve --host=127.0.0.1 --port=8000
