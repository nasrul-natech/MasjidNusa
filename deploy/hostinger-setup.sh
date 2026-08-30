#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────
# MasjidNusa — Setup awal di Hostinger
# Jalankan di server: bash deploy/hostinger-setup.sh
# Lokasi: ~/domains/masjid.nusasuite.id
# ─────────────────────────────────────────────────────────────
set -euo pipefail

DOMAIN_DIR="${DOMAIN_DIR:-$HOME/domains/masjid.nusasuite.id}"
APP_DIR="$DOMAIN_DIR/app"
REPO="${REPO:-git@github.com:nasrul-natech/MasjidNusa.git}"
BRANCH="${BRANCH:-main}"

echo "==> MasjidNusa Hostinger Setup"
echo "    Domain dir : $DOMAIN_DIR"
echo "    App dir    : $APP_DIR"
echo ""

mkdir -p "$DOMAIN_DIR"
cd "$DOMAIN_DIR"

# ── 1. Bersihkan placeholder Hostinger ──────────────────────
echo "==> [1/8] Bersihkan folder awal..."
rm -f DO_NOT_UPLOAD_HERE

if [ -L public_html ]; then
  rm public_html
elif [ -d public_html ]; then
  # Hapus hanya jika kosong; jika ada isi, backup dulu
  if [ -z "$(ls -A public_html 2>/dev/null)" ]; then
    rmdir public_html
  else
    echo "    public_html tidak kosong — backup ke public_html.bak"
    mv public_html "public_html.bak.$(date +%Y%m%d%H%M%S)"
  fi
fi

# ── 2. Clone / update source ke app/ ──────────────────────────
echo "==> [2/8] Clone / update source code..."
if [ -d "$APP_DIR/.git" ]; then
  git -C "$APP_DIR" fetch origin
  git -C "$APP_DIR" checkout "$BRANCH"
  git -C "$APP_DIR" pull origin "$BRANCH"
else
  git clone --branch "$BRANCH" "$REPO" "$APP_DIR"
fi

# ── 3. Symlink public_html → app/public ───────────────────────
echo "==> [3/8] Symlink public_html → app/public..."
ln -sfn app/public public_html
ls -la public_html

# ── 4. Composer ───────────────────────────────────────────────
echo "==> [4/8] Composer install..."
cd "$APP_DIR"
if ! command -v composer >/dev/null 2>&1; then
  echo "    composer tidak ditemukan — coba: php ~/composer.phar install"
  php ~/composer.phar install --no-dev --optimize-autoloader --no-interaction
else
  composer install --no-dev --optimize-autoloader --no-interaction
fi

# ── 5. Environment ────────────────────────────────────────────
echo "==> [5/8] Environment (.env)..."
if [ ! -f .env ]; then
  cp .env.example .env
  php artisan key:generate --force
  echo ""
  echo "    ⚠️  Edit .env sekarang sebelum lanjut:"
  echo "       nano $APP_DIR/.env"
  echo ""
  echo "    Set minimal:"
  echo "      APP_ENV=production"
  echo "      APP_DEBUG=false"
  echo "      APP_URL=https://masjid.nusasuite.id"
  echo "      DB_* (dari hPanel → Databases)"
  echo ""
  read -r -p "    Tekan Enter setelah .env selesai diedit..." _
fi

# ── 6. Storage symlink ────────────────────────────────────────
echo "==> [6/8] Storage symlink (public/storage → storage/app/public)..."
php artisan storage:link --force 2>/dev/null || php artisan storage:link

# ── 7. Permission ─────────────────────────────────────────────
echo "==> [7/8] Set permission storage & cache..."
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

# ── 8. Build assets (opsional) ────────────────────────────────
echo "==> [8/8] Build frontend (opsional)..."
if command -v npm >/dev/null 2>&1; then
  npm ci --omit=dev 2>/dev/null || npm install --omit=dev
  npm run build
else
  echo "    npm tidak tersedia — build di lokal lalu rsync public/build ke server"
fi

# ── Migrate & cache ───────────────────────────────────────────
echo "==> Migrate & optimize..."
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "✅ Setup selesai!"
echo ""
echo "Struktur:"
echo "  $DOMAIN_DIR/app/          ← Laravel (di luar web root)"
echo "  $DOMAIN_DIR/public_html   → app/public (web root Hostinger)"
echo ""
echo "Cron (hPanel → Advanced → Cron Jobs):"
echo "  * * * * * cd $APP_DIR && php artisan schedule:run >> /dev/null 2>&1"
echo ""
