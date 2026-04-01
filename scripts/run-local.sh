#!/usr/bin/env bash
# Bavly KYC — one-shot local setup + serve (PHP 8.3+, Composer, Node required)
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

export PATH="/opt/homebrew/bin:/usr/local/bin:$PATH"

for bin in php composer npm; do
  command -v "$bin" >/dev/null 2>&1 || { echo "Missing: $bin — install PHP 8.3+, Composer, and Node.js."; exit 1; }
done

if [[ ! -f .env ]]; then
  if [[ -f .env.sqlite.example ]]; then
    cp .env.sqlite.example .env
    echo "Created .env from .env.sqlite.example (SQLite)."
  else
    cp .env.example .env
    echo "Created .env from .env.example — set DB_* for MySQL or switch to SQLite."
  fi
fi

mkdir -p database
touch database/database.sqlite

composer install --no-interaction
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force

npm install --no-fund
npm run build

echo ""
echo "Starting server at http://127.0.0.1:8000"
echo "Login: superadmin / (see DatabaseSeeder or SEED_ADMIN_PASSWORD in .env)"
php artisan serve --host=127.0.0.1 --port=8000
