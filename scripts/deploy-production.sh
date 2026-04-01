#!/usr/bin/env bash
# Run on the server after git pull — idempotent caches & optional migrations
# Usage: bash scripts/deploy-production.sh [--migrate]
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

export PATH="/usr/local/bin:/usr/bin:$PATH"

if ! command -v php >/dev/null 2>&1; then
  echo "php not found"; exit 1
fi

composer install --no-dev --optimize-autoloader --no-interaction

if [[ "${1:-}" == "--migrate" ]]; then
  php artisan migrate --force
fi

npm ci --no-fund --no-audit 2>/dev/null && npm run build || {
  echo "Note: npm build skipped or failed — upload public/build if building off-server."
}

composer run deploy:optimize --no-interaction

echo "Done. Test login and one KYC flow (see docs/DEPLOYMENT.md §8)."
