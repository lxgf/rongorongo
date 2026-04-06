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

# 2. Generate APP_KEY on host if empty
APP_KEY=$(grep '^APP_KEY=' .env | cut -d= -f2-)
if [ -z "$APP_KEY" ]; then
    echo "Generating APP_KEY..."
    NEW_KEY="base64:$(openssl rand -base64 32)"
    sed -i "s|^APP_KEY=.*|APP_KEY=$NEW_KEY|" .env
    echo "  Key set: $NEW_KEY"
fi

# 3. Build and start
echo ""
echo "=== Building and starting containers ==="
docker compose up -d --build
echo "Waiting for services..."
sleep 5

# 4. Copy .env into container and cache config
echo ""
echo "=== Configuring app ==="
docker compose cp .env app:/var/www/html/.env
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
docker compose exec -T app php artisan view:cache
docker compose restart app
sleep 3

# 5. Run migrations
echo ""
echo "=== Running migrations ==="
docker compose exec -T app php artisan migrate --force

# 6. Restore corpus dump
if [ -f database/dumps/corpus.sql ]; then
    echo ""
    echo "=== Restoring corpus dump ==="
    docker compose exec -T db psql -U rongorongo -d rongorongo --single-transaction < database/dumps/corpus.sql 2>&1 | tail -3
    echo "  Dump restored"
fi

# 7. Seed
echo ""
echo "=== Seeding ==="
docker compose exec -T app php artisan db:seed --force

# 8. Final restart
echo ""
echo "=== Final restart ==="
docker compose restart app varnish
sleep 3

# 9. Health checks
echo ""
echo "=== Health checks ==="

FAIL=0
check_url() {
    local url=$1
    local label=$2
    local status=$(curl -s -o /dev/null -w "%{http_code}" "$url" 2>/dev/null)
    if [ "$status" = "200" ]; then
        echo "  OK  $status  $label"
    else
        echo "  ERR $status  $label"
        FAIL=1
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

echo ""
echo "=== Container status ==="
docker compose ps --format "table {{.Name}}\t{{.Status}}\t{{.Ports}}"

if [ "$FAIL" = "1" ]; then
    echo ""
    echo "=== ERRORS detected. Checking logs... ==="
    docker compose logs app --tail=10
    exit 1
fi

echo ""
echo "=== Deploy complete ==="
