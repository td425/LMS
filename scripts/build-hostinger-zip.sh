#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$ROOT/learnhost-hostinger.zip"
STAGING="$ROOT/.hostinger-staging"

echo "==> Building LearnHost Hostinger package..."

cd "$ROOT"

if ! command -v composer >/dev/null 2>&1; then
  echo "Composer is required on your PC to build the package."
  exit 1
fi

if ! command -v npm >/dev/null 2>&1; then
  echo "npm is required on your PC to build frontend assets."
  exit 1
fi

echo "==> Installing PHP dependencies (production)..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Building frontend assets..."
npm install --ignore-scripts
npm run build

echo "==> Creating zip (includes vendor/ + public/build/)..."
rm -rf "$STAGING" "$OUT"
mkdir -p "$STAGING"

tar -cf - \
  --exclude='.git' \
  --exclude='.env' \
  --exclude='node_modules' \
  --exclude='.hostinger-staging' \
  --exclude='learnhost-hostinger.zip' \
  --exclude='tests' \
  --exclude='.phpunit.cache' \
  -C "$ROOT" . | tar -xf - -C "$STAGING"

cd "$STAGING"
zip -rq "$OUT" .

cd "$ROOT"
rm -rf "$STAGING"

SIZE=$(du -h "$OUT" | cut -f1)
echo ""
echo "Done! Package created: $OUT ($SIZE)"
echo ""
echo "Upload to Hostinger, extract, then run on server:"
echo "  cp .env.example .env"
echo "  php artisan key:generate"
echo "  php artisan migrate --seed --force"
echo "  php artisan storage:link"
echo "  chmod -R 775 storage bootstrap/cache"
