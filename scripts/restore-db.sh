#!/usr/bin/env bash
# Restore backup pg_dump (-Fc) — docs/09 §6.4.
#
# Pemakaian (di HOST, dari root proyek):
#   scripts/restore-db.sh backups/pmr-db-daily-<ts>.dump pmr_restore_test  # UJI (aman)
#   scripts/restore-db.sh <file.dump>                    # TIMPA DB pmr (konfirmasi ketik YES)
#
# Opsional:
#   PMR_COMPOSE_FILE=docker-compose.yml   # paksa file compose (default: otomatis)
#
# Catatan: restore ke `pmr` memutus koneksi aplikasi sesaat (DROP ... WITH FORCE)
# dan MENIMPA seluruh isi database — jalankan worker/scheduler berhenti dulu bila perlu.
set -euo pipefail

FILE="${1:-}"
TARGET="${2:-pmr}"

if [ -z "$FILE" ] || [ ! -f "$FILE" ]; then
    echo "Usage: $0 <file.dump> [target_db]" >&2
    echo "  file harus berupa hasil pg_dump (-Fc)." >&2
    exit 1
fi

case "$TARGET" in
    *[!a-z0-9_]*) echo "Nama DB tidak valid: $TARGET" >&2; exit 1 ;;
esac

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT_DIR"

# File compose: pilih yang container postgres-nya benar-benar berjalan.
COMPOSE_FILE="${PMR_COMPOSE_FILE:-}"
if [ -z "$COMPOSE_FILE" ]; then
    for f in docker-compose.prod.yml docker-compose.yml; do
        if [ -f "$ROOT_DIR/$f" ] \
            && docker compose -f "$ROOT_DIR/$f" ps -q postgres 2>/dev/null | grep -q .; then
            COMPOSE_FILE="$f"
            break
        fi
    done
fi
if [ -z "$COMPOSE_FILE" ]; then
    echo "ERROR: service postgres tidak ditemukan berjalan." >&2
    echo "Jalankan dulu: docker compose up -d" >&2
    echo "(atau set PMR_COMPOSE_FILE=docker-compose.yml)" >&2
    exit 1
fi

compose() { docker compose -f "$ROOT_DIR/$COMPOSE_FILE" "$@"; }
log() { printf '[%s] %s\n' "$(date '+%F %T')" "$*"; }

if [ "$TARGET" = "pmr" ]; then
    log "PERINGATAN: akan MENIMPA database `pmr` — koneksi aplikasi diputus sesaat."
    printf "Ketik YES untuk lanjut: "
    read -r answer
    if [ "$answer" != "YES" ]; then
        echo "Dibatalkan."
        exit 1
    fi
fi

log "drop database $TARGET (bila ada, FORCE)"
compose exec -e R_TARGET="$TARGET" -T postgres \
    sh -c 'psql -U "$POSTGRES_USER" -d postgres -c "DROP DATABASE IF EXISTS $R_TARGET WITH (FORCE)"'

log "create database $TARGET"
compose exec -e R_TARGET="$TARGET" -T postgres \
    sh -c 'createdb -U "$POSTGRES_USER" "$R_TARGET"'

log "pg_restore ← $FILE"
compose exec -e R_TARGET="$TARGET" -T postgres \
    sh -c 'pg_restore -U "$POSTGRES_USER" -d "$R_TARGET" --no-owner --no-privileges' < "$FILE"

log "sanity check (jumlah baris inti):"
compose exec -e R_TARGET="$TARGET" -T postgres sh -c 'psql -U "$POSTGRES_USER" -d "$R_TARGET" -c "
SELECT
  (SELECT COUNT(*) FROM users)    AS users,
  (SELECT COUNT(*) FROM members)  AS members,
  (SELECT COUNT(*) FROM events)   AS events,
  (SELECT COUNT(*) FROM settings) AS settings;
"'

log "restore selesai → $TARGET"
