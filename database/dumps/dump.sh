#!/bin/bash
# Dump corpus data from Docker PostgreSQL (excludes sensitive tables)
# Usage: ./database/dumps/dump.sh

set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/../.." && pwd)"
DUMP_FILE="$SCRIPT_DIR/corpus.sql"

cd "$PROJECT_DIR"

echo "Dumping corpus data..."

docker compose -f docker-compose.yml -f docker-compose.dev.yml exec -T db pg_dump \
  -U rongorongo \
  --no-owner \
  --no-privileges \
  --exclude-table=users \
  --exclude-table=sessions \
  --exclude-table=personal_access_tokens \
  --exclude-table=password_reset_tokens \
  --exclude-table=failed_jobs \
  --exclude-table=jobs \
  --exclude-table=job_batches \
  --exclude-table=cache \
  --exclude-table=cache_locks \
  rongorongo > "$DUMP_FILE"

SIZE=$(du -sh "$DUMP_FILE" | cut -f1)
LINES=$(wc -l < "$DUMP_FILE")
echo "Done: $DUMP_FILE ($SIZE, $LINES lines)"
