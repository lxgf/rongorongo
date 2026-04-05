#!/bin/bash
# Restore corpus data into Docker PostgreSQL
# Usage: ./database/dumps/restore.sh

set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/../.." && pwd)"
DUMP_FILE="$SCRIPT_DIR/corpus.sql"

if [ ! -f "$DUMP_FILE" ]; then
  echo "Error: $DUMP_FILE not found"
  exit 1
fi

cd "$PROJECT_DIR"

COMPOSE="docker compose"
if [ -f docker-compose.dev.yml ]; then
  COMPOSE="docker compose -f docker-compose.yml -f docker-compose.dev.yml"
fi

echo "Running migrations..."
$COMPOSE exec -T app php artisan migrate --force

echo "Restoring corpus data from $DUMP_FILE..."
$COMPOSE exec -T db psql \
  -U rongorongo \
  -d rongorongo \
  --single-transaction \
  < "$DUMP_FILE"

echo "Seeding admin user..."
$COMPOSE exec -T app php artisan db:seed --force

echo "Restore complete."
