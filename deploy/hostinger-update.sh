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

# Jangan hapus public_html. Update berhenti bila symlink tidak mengarah ke app/public.
cd "$DOMAIN_DIR"
if [ ! -L public_html ] || [ "$(readlink public_html)" != "app/public" ]; then
  echo "ERROR: public_html harus symlink ke app/public."
  echo "       Update dihentikan. Folder yang ada tidak dihapus."
  exit 1
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

echo "==> Migrate (tambah tabel baru saja, data yang ada tidak dihapus)..."
artisan_safe migrate --force --no-interaction

echo "==> Segarkan cache rute dan tampilan..."
"$PHP_BIN" artisan route:clear
"$PHP_BIN" artisan view:clear

echo "==> Storage link..."
"$PHP_BIN" artisan storage:link --force || "$PHP_BIN" artisan storage:link

if [ -f package.json ]; then
  npm_build_assets
fi

# Verifikasi CSS/JS dari manifest ada di disk (cegah halaman tanpa Tailwind)
if [ -f public/build/manifest.json ]; then
  missing=0
  while IFS= read -r asset; do
    if [ -n "$asset" ] && [ ! -f "public/build/$asset" ]; then
      echo "    ⚠️  Asset hilang: public/build/$asset"
      missing=1
    fi
  done < <(grep -o '"file": "assets/[^"]*"' public/build/manifest.json | sed 's/"file": "//;s/"//')
  if [ "$missing" -eq 1 ]; then
    echo ""
    echo "    ERROR: File CSS/JS build tidak lengkap."
    echo "    Dari komputer lokal jalankan: npm run build && bash deploy/sync-assets.sh"
    echo "    Atau commit + pull file CSS yang ada di manifest.json."
    exit 1
  fi
fi

echo "==> Cache..."
"$PHP_BIN" artisan config:clear
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache

echo "==> Maintenance off..."
"$PHP_BIN" artisan up 2>/dev/null || true

echo "✅ Update selesai — https://masjid.nusasuite.id"
