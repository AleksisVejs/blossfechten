#!/usr/bin/env bash
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
BACKEND_DIR="$REPO_ROOT/backend"
FRONTEND_DIR="$REPO_ROOT/frontend"

# Update these if your cPanel paths differ.
# The addon domain's document root is the build output itself
# (frontend/dist), so we serve in place — no copy step.
FRONTEND_PUBLIC_PATH="${FRONTEND_PUBLIC_PATH:-$FRONTEND_DIR/dist}"
API_SYMLINK_PATH="${API_SYMLINK_PATH:-$FRONTEND_PUBLIC_PATH/api}"

# Safety: never let this script wipe the primary domain or the repo root.
TARGET_RESOLVED="$(cd "$FRONTEND_PUBLIC_PATH" 2>/dev/null && pwd || echo "$FRONTEND_PUBLIC_PATH")"
if [ "$TARGET_RESOLVED" = "$(cd "$HOME/public_html" 2>/dev/null && pwd)" ]; then
  echo "[deploy] ERROR: FRONTEND_PUBLIC_PATH resolves to \$HOME/public_html (primary domain root). Refusing."
  exit 1
fi
if [ "$TARGET_RESOLVED" = "$REPO_ROOT" ] || [ "$TARGET_RESOLVED" = "$FRONTEND_DIR" ]; then
  echo "[deploy] ERROR: FRONTEND_PUBLIC_PATH ($TARGET_RESOLVED) overlaps the repo. Refusing — rsync --delete would erase the source."
  exit 1
fi
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

DIST_DIR="$FRONTEND_DIR/dist"
DIST_REALPATH="$(cd "$DIST_DIR" && pwd)"

if [ ! -d "$FRONTEND_PUBLIC_PATH" ]; then
  echo "[deploy] ERROR: Frontend publish path '$FRONTEND_PUBLIC_PATH' does not exist"
  exit 1
fi

TARGET_REALPATH="$(cd "$FRONTEND_PUBLIC_PATH" && pwd)"
if [ "$TARGET_REALPATH" = "$DIST_REALPATH" ]; then
  echo "[deploy] Frontend publish path is dist; skipping copy step."
else
  echo "[deploy] Publishing frontend dist to $FRONTEND_PUBLIC_PATH"
  if command -v rsync >/dev/null 2>&1; then
    rsync -a --delete "$DIST_DIR/" "$FRONTEND_PUBLIC_PATH/"
  else
    rm -rf "$FRONTEND_PUBLIC_PATH"/*
    cp -a "$DIST_DIR/." "$FRONTEND_PUBLIC_PATH/"
  fi
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
