#!/usr/bin/env bash
# Jalankan dari komputer lokal (setelah npm run build)
# Upload public/build ke server Hostinger
set -euo pipefail

SSH_HOST="${SSH_HOST:-u568257859@46.202.136.199}"
SSH_PORT="${SSH_PORT:-65002}"
REMOTE_APP="${REMOTE_APP:-domains/masjid.nusasuite.id/app}"

cd "$(dirname "$0")/.."

if [ ! -f public/build/manifest.json ]; then
  echo "Build belum ada — jalankan: npm run build"
  exit 1
fi

echo "==> Upload public/build ke server..."
rsync -avz --delete -e "ssh -p $SSH_PORT" \
  public/build/ \
  "$SSH_HOST:$REMOTE_APP/public/build/"

echo "✅ Assets ter-upload"
