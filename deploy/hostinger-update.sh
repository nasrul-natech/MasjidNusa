#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────
# MasjidNusa — Update rutin di Hostinger
# Jalankan di server: bash deploy/hostinger-update.sh
# ─────────────────────────────────────────────────────────────
set -euo pipefail

DOMAIN_DIR="${DOMAIN_DIR:-$HOME/domains/masjid.nusasuite.id}"
APP_DIR="$DOMAIN_DIR/app"
BRANCH="${BRANCH:-main}"

echo "==> MasjidNusa Update"
cd "$APP_DIR"

# Pastikan symlink public_html masih benar
cd "$DOMAIN_DIR"
if [ ! -L public_html ] || [ "$(readlink public_html)" != "app/public" ]; then
  echo "==> Perbaiki symlink public_html..."
  rm -rf public_html
  ln -sfn app/public public_html
fi

cd "$APP_DIR"
echo "==> Git pull..."
git fetch origin
git checkout "$BRANCH"
git pull origin "$BRANCH"

echo "==> Composer..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Migrate..."
php artisan migrate --force

echo "==> Storage link..."
php artisan storage:link --force 2>/dev/null || true

if command -v npm >/dev/null 2>&1 && [ -f package.json ]; then
  echo "==> Build assets..."
  npm ci --omit=dev 2>/dev/null || npm install --omit=dev
  npm run build
fi

echo "==> Cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Maintenance off..."
php artisan up 2>/dev/null || true

echo "✅ Update selesai — https://masjid.nusasuite.id"
