#!/usr/bin/env bash
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
BACKEND_DIR="$REPO_ROOT/backend"
FRONTEND_DIR="$REPO_ROOT/frontend"

# Update these if your cPanel paths differ.
API_SYMLINK_PATH="${API_SYMLINK_PATH:-$HOME/api}"
FRONTEND_PUBLIC_PATH="${FRONTEND_PUBLIC_PATH:-$HOME/public_html}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"

echo "[deploy] Starting deployment from $REPO_ROOT"

if ! command -v "$PHP_BIN" >/dev/null 2>&1; then
  echo "[deploy] ERROR: PHP binary '$PHP_BIN' not found"
  exit 1
fi

if ! command -v "$COMPOSER_BIN" >/dev/null 2>&1; then
  echo "[deploy] ERROR: Composer binary '$COMPOSER_BIN' not found"
  exit 1
fi

if ! command -v npm >/dev/null 2>&1; then
  echo "[deploy] ERROR: npm not found. Install Node.js in cPanel first."
  exit 1
fi

echo "[deploy] Installing backend dependencies"
cd "$BACKEND_DIR"
"$COMPOSER_BIN" install --no-dev --prefer-dist --no-interaction --optimize-autoloader

if [ ! -f ".env" ]; then
  echo "[deploy] ERROR: backend/.env not found on server"
  exit 1
fi

echo "[deploy] Running Laravel maintenance commands"
"$PHP_BIN" artisan migrate --force
"$PHP_BIN" artisan storage:link || true
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache

echo "[deploy] Building frontend"
cd "$FRONTEND_DIR"
npm ci
npm run build

if [ ! -d "$FRONTEND_PUBLIC_PATH" ]; then
  echo "[deploy] ERROR: Frontend publish path '$FRONTEND_PUBLIC_PATH' does not exist"
  exit 1
fi

echo "[deploy] Publishing frontend dist to $FRONTEND_PUBLIC_PATH"
if command -v rsync >/dev/null 2>&1; then
  rsync -a --delete "$FRONTEND_DIR/dist/" "$FRONTEND_PUBLIC_PATH/"
else
  rm -rf "$FRONTEND_PUBLIC_PATH"/*
  cp -a "$FRONTEND_DIR/dist/." "$FRONTEND_PUBLIC_PATH/"
fi

if [ -d "$BACKEND_DIR/public/storage" ]; then
  if command -v rsync >/dev/null 2>&1; then
    rsync -a "$BACKEND_DIR/public/storage/" "$FRONTEND_PUBLIC_PATH/storage/"
  else
    mkdir -p "$FRONTEND_PUBLIC_PATH/storage"
    cp -a "$BACKEND_DIR/public/storage/." "$FRONTEND_PUBLIC_PATH/storage/"
  fi
fi

if [ -e "$API_SYMLINK_PATH" ] && [ ! -L "$API_SYMLINK_PATH" ]; then
  echo "[deploy] ERROR: $API_SYMLINK_PATH exists and is not a symlink"
  exit 1
fi

ln -sfn "$BACKEND_DIR/public" "$API_SYMLINK_PATH"

echo "[deploy] Deployment finished successfully"
