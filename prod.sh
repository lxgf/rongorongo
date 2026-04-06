#!/bin/bash
# Production deploy script
# Usage: ./prod.sh
set -e

PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$PROJECT_DIR"

echo "=== Rongorongo Production Deploy ==="

# 1. Create .env if missing
if [ ! -f .env ]; then
    echo "Creating .env..."
    cat > .env << 'ENVEOF'
APP_NAME=Rongorongo
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost:2020

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=rongorongo
DB_USERNAME=rongorongo
DB_PASSWORD=secret

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=redis
REDIS_PORT=6379
ENVEOF
    echo "  .env created"
fi

# 2. Build and start
echo ""
echo "=== Building and starting containers ==="
docker compose up -d --build

echo ""
echo "=== Waiting for services ==="
sleep 5

# 3. Copy .env into container
echo "Copying .env into app container..."
docker compose cp .env app:/var/www/html/.env

# 4. Generate key if empty
APP_KEY=$(grep '^APP_KEY=' .env | cut -d= -f2-)
if [ -z "$APP_KEY" ]; then
    echo "Generating APP_KEY..."
    docker compose exec -T app php artisan key:generate --force
    # Copy key back to host .env
    NEW_KEY=$(docker compose exec -T app php artisan key:generate --show)
    sed -i "s|^APP_KEY=.*|APP_KEY=$NEW_KEY|" .env
    echo "  Key: $NEW_KEY"
fi

# 5. Restart app with new .env
echo ""
echo "=== Restarting app ==="
docker compose restart app
sleep 3

# 6. Run migrations
echo ""
echo "=== Running migrations ==="
docker compose exec -T app php artisan migrate --force

# 7. Restore corpus dump
if [ -f database/dumps/corpus.sql ]; then
    echo ""
    echo "=== Restoring corpus dump ==="
    docker compose exec -T db psql -U rongorongo -d rongorongo --single-transaction < database/dumps/corpus.sql
    echo "  Dump restored"
fi

# 8. Seed
echo ""
echo "=== Seeding ==="
docker compose exec -T app php artisan db:seed --force

# 9. Clear caches
echo ""
echo "=== Clearing caches ==="
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
docker compose exec -T app php artisan view:cache

# 10. Health checks
echo ""
echo "=== Health checks ==="
sleep 2

check_url() {
    local url=$1
    local label=$2
    local status=$(curl -s -o /dev/null -w "%{http_code}" "$url" 2>/dev/null)
    if [ "$status" = "200" ]; then
        echo "  OK  $status  $label"
    else
        echo "  ERR $status  $label"
    fi
}

BASE="http://localhost:2020"
check_url "$BASE/"                  "Homepage"
check_url "$BASE/glyph/001"        "Glyph 001"
check_url "$BASE/tablet/A"         "Tablet A"
check_url "$BASE/tablets"           "Tablets list"
check_url "$BASE/ligatures"         "Ligatures"
check_url "$BASE/renderings"        "Renderings"
check_url "$BASE/lines/A"           "Lines A"
check_url "$BASE/line/A/a/1"        "Line A/a/1"
check_url "$BASE/about"             "About"
check_url "$BASE/sitemap.xml"       "Sitemap"
check_url "$BASE/favicon.svg"       "Favicon SVG"

echo ""
echo "=== Container status ==="
docker compose ps

echo ""
echo "=== Done ==="
