#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────
# MasjidNusa — Update rutin di Hostinger
# Jalankan di server: bash deploy/hostinger-update.sh
# ─────────────────────────────────────────────────────────────
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=hostinger-php.sh
source "$SCRIPT_DIR/hostinger-php.sh"

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
if command -v composer >/dev/null 2>&1; then
  composer install --no-dev --optimize-autoloader --no-interaction
elif command -v composer2 >/dev/null 2>&1; then
  composer2 install --no-dev --optimize-autoloader --no-interaction
else
  "$PHP_BIN" "$HOME/composer.phar" install --no-dev --optimize-autoloader --no-interaction
fi

echo "==> Migrate..."
"$PHP_BIN" artisan migrate --force

echo "==> Storage link..."
"$PHP_BIN" artisan storage:link --force 2>/dev/null || true

if command -v npm >/dev/null 2>&1 && [ -f package.json ]; then
  echo "==> Build assets..."
  npm ci --omit=dev 2>/dev/null || npm install --omit=dev
  npm run build
fi

echo "==> Cache..."
"$PHP_BIN" artisan config:clear
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache

echo "==> Maintenance off..."
"$PHP_BIN" artisan up 2>/dev/null || true

echo "✅ Update selesai — https://masjid.nusasuite.id"
