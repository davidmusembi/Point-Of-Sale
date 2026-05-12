#!/usr/bin/env bash
# =============================================================================
# deploy.sh — Zero-downtime deployment for ultimatePOS
# =============================================================================
# Usage: bash deploy.sh [--skip-migrations]
# Run as the deploy user (not root). Assumes:
#   - App lives at /var/www/pos
#   - PHP 8.1 + Composer in PATH
#   - Supervisor is managing queue workers
#   - Redis is running for cache/queue
# =============================================================================

set -euo pipefail

APP_DIR="/var/www/pos"
SKIP_MIGRATIONS="${1:-}"

echo "==> Pulling latest code..."
git -C "$APP_DIR" pull origin main

echo "==> Installing/updating PHP dependencies (no dev, optimized autoloader)..."
composer install \
    --no-interaction \
    --no-dev \
    --optimize-autoloader \
    --working-dir="$APP_DIR"

echo "==> Installing/building frontend assets..."
cd "$APP_DIR"
npm ci --omit=dev
npm run build

echo "==> Putting application into maintenance mode..."
php "$APP_DIR/artisan" down --render="errors::503" --retry=60

echo "==> Clearing compiled views and config cache..."
php "$APP_DIR/artisan" view:clear
php "$APP_DIR/artisan" config:clear
php "$APP_DIR/artisan" route:clear
php "$APP_DIR/artisan" event:clear

if [[ "$SKIP_MIGRATIONS" != "--skip-migrations" ]]; then
    echo "==> Running central database migrations..."
    php "$APP_DIR/artisan" migrate --force

    echo "==> Running tenant database migrations..."
    php "$APP_DIR/artisan" tenants:migrate --force
fi

echo "==> Warming up caches..."
php "$APP_DIR/artisan" config:cache
php "$APP_DIR/artisan" route:cache
php "$APP_DIR/artisan" view:cache

echo "==> Restarting queue workers (graceful — workers finish current job first)..."
php "$APP_DIR/artisan" queue:restart

echo "==> Reloading Supervisor..."
supervisorctl update
supervisorctl restart pos-worker-default:*
supervisorctl restart pos-worker-tenants:*

echo "==> Bringing application back online..."
php "$APP_DIR/artisan" up

echo ""
echo "Deployment complete."
