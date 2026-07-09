#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SEED=false

for arg in "$@"; do
  case "$arg" in
    --seed)
      SEED=true
      ;;
    -h|--help)
      echo "Usage: bash scripts/deploy-ubuntu.sh [--seed]"
      echo ""
      echo "  --seed   Run database seeders (first install only)"
      exit 0
      ;;
    *)
      echo "Unknown option: $arg"
      echo "Usage: bash scripts/deploy-ubuntu.sh [--seed]"
      exit 1
      ;;
  esac
done

cd "$ROOT"

echo "==> LearnHost LMS — Ubuntu deploy"

if ! command -v php >/dev/null 2>&1; then
  echo "Error: php is not installed."
  exit 1
fi

if ! command -v composer >/dev/null 2>&1; then
  echo "Error: composer is not installed."
  exit 1
fi

if [[ ! -f .env ]]; then
  echo "Error: .env not found. Copy .env.example and configure it first:"
  echo "  cp .env.example .env"
  echo "  php artisan key:generate"
  exit 1
fi

echo "==> Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Running migrations..."
if [[ "$SEED" == true ]]; then
  php artisan migrate --seed --force
else
  php artisan migrate --force
fi

echo "==> Linking storage..."
php artisan storage:link 2>/dev/null || true

echo "==> Setting permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "==> Caching config, routes, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "Done! Deploy complete."
if [[ "$SEED" == true ]]; then
  echo "Demo logins (password: password): admin@lms.test, instructor@lms.test, student@lms.test"
  echo "Change these passwords on a live site."
fi
