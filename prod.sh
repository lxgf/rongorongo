#!/bin/bash
# Production deploy script
# Usage: ./prod.sh
set -e

PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$PROJECT_DIR"

echo "=== Rongorongo Production Deploy ==="

# 1. Create .env.docker if missing (docker-compose.yml reads env_file: .env.docker)
if [ ! -f .env.docker ]; then
    echo "Creating .env.docker..."
    NEW_KEY="base64:$(openssl rand -base64 32)"
    cat > .env.docker << ENVEOF
APP_NAME=Rongorongo
APP_ENV=production
APP_KEY=${NEW_KEY}
APP_DEBUG=false
APP_URL=http://localhost:2020
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
FAKER_LOCALE=ru_RU
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
MAIL_MAILER=log
PHP_CLI_SERVER_WORKERS=4
ENVEOF
    echo "  .env.docker created with key: ${NEW_KEY}"
else
    # Ensure APP_KEY is not a placeholder
    APP_KEY=$(grep '^APP_KEY=' .env.docker | cut -d= -f2-)
    if [ -z "$APP_KEY" ] || echo "$APP_KEY" | grep -q "REPLACE"; then
        NEW_KEY="base64:$(openssl rand -base64 32)"
        sed -i "s|^APP_KEY=.*|APP_KEY=${NEW_KEY}|" .env.docker
        echo "  APP_KEY was placeholder, regenerated: ${NEW_KEY}"
    fi
fi

# 2. Build and start
echo ""
echo "=== Building and starting containers ==="
docker compose up -d --build --force-recreate

echo ""
echo "=== Waiting for services ==="
sleep 8

# 3. Run migrations
echo ""
echo "=== Running migrations ==="
docker compose exec -T app php artisan migrate --force

# 4. Restore corpus dump if DB is empty
GLYPH_COUNT=$(docker compose exec -T db psql -U rongorongo -d rongorongo -t -c "SELECT count(*) FROM glyphs;" 2>/dev/null | tr -d ' ')
if [ "$GLYPH_COUNT" = "0" ] || [ -z "$GLYPH_COUNT" ]; then
    if [ -f database/dumps/corpus.sql ]; then
        echo ""
        echo "=== Restoring corpus dump (DB is empty) ==="
        docker compose exec -T app php artisan migrate:fresh --force
        docker compose exec -T db psql -U rongorongo -d rongorongo < database/dumps/corpus.sql 2>&1 | grep -c 'COPY\|ERROR' | xargs -I{} echo "  {} statements"
        echo "  Dump restored"
    fi
else
    echo ""
    echo "=== DB already has data ($GLYPH_COUNT glyphs), skipping dump restore ==="
fi

# 5. Seed
echo ""
echo "=== Seeding ==="
docker compose exec -T app php artisan db:seed --force

# 6. Cache config
echo ""
echo "=== Caching config ==="
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
docker compose exec -T app php artisan view:cache

# 7. Final restart to pick up cached config
echo ""
echo "=== Final restart ==="
docker compose up -d --force-recreate app
sleep 5

# 8. Health checks
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
docker compose ps

if [ "$FAIL" = "1" ]; then
    echo ""
    echo "=== ERRORS detected. Last 15 log lines: ==="
    docker compose logs app --tail=15
    exit 1
fi

echo ""
echo "=== Deploy complete ==="
