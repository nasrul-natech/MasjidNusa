#!/usr/bin/env bash
# Deteksi PHP CLI 8.4+ di Hostinger (CloudLinux alt-php)
# Usage: source deploy/hostinger-php.sh

hostinger_find_php() {
  local candidates=(
    php84
    php83
    /opt/alt/php84/usr/bin/php
    /opt/alt/php83/usr/bin/php
    /usr/local/bin/php84
    /usr/local/bin/php83
    php
  )

  for bin in "${candidates[@]}"; do
    if command -v "$bin" >/dev/null 2>&1; then
      local ver
      ver=$("$bin" -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo "0.0")
      local major minor
      major=${ver%%.*}
      minor=${ver#*.}
      if [ "$major" -gt 8 ] || { [ "$major" -eq 8 ] && [ "$minor" -ge 3 ]; }; then
        echo "$bin"
        return 0
      fi
    fi
  done

  return 1
}

PHP_BIN="${PHP_BIN:-$(hostinger_find_php || true)}"

if [ -z "$PHP_BIN" ]; then
  echo "ERROR: PHP 8.3+ CLI tidak ditemukan."
  echo "       Web PHP sudah 8.4 di hPanel, tapi SSH masih pakai versi lama."
  echo "       Coba: php84 -v  atau  /opt/alt/php84/usr/bin/php -v"
  exit 1
fi

export PHP_BIN
echo "==> PHP CLI: $PHP_BIN ($($PHP_BIN -r 'echo PHP_VERSION;'))"

# Tolak perintah yang mengosongkan database produksi.
artisan_safe() {
  local joined="$*"
  case "$joined" in
    *migrate:fresh*|*migrate:refresh*|*migrate:reset*|*db:wipe*|*schema:drop*|*db:seed*)
      echo "DITOLAK: '$joined' bisa menghapus isi database. Tidak dijalankan."
      exit 1
      ;;
  esac
  "$PHP_BIN" artisan "$@"
}

# Lewati npm bila aset sudah ada. Kalau belum, pasang dependency Vite lalu build.
npm_build_assets() {
  if [ -f public/build/manifest.json ]; then
    echo "==> public/build sudah ada — npm dilewati."
    return 0
  fi

  if ! command -v npm >/dev/null 2>&1; then
    echo "ERROR: public/build belum ada dan npm tidak tersedia. Build di lokal lalu unggah public/build."
    exit 1
  fi

  echo "==> Build assets (termasuk dependency Vite)..."
  if [ -f package-lock.json ]; then
    npm ci --include=dev
  else
    npm install --include=dev
  fi
  npm run build
}
