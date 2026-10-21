#!/usr/bin/env bash
# Backup DB (pg_dump -Fc) + storage (tar.gz) — docs/09 §6.3.
# Pemakaian (cron di VPS):
#   0 1 * * *   /opt/pmr-system/scripts/backup-db.sh daily
#   0 2 * * 0   /opt/pmr-system/scripts/backup-db.sh weekly
#   0 3 1 * *   /opt/pmr-system/scripts/backup-db.sh monthly
#
# Opsional (di .env VPS / environment cron):
#   BACKUP_DIR             lokasi file lokal   (default: <root proyek>/backups)
#   BACKUP_GPG_RECIPIENT   fingerprint/key gpg → enkripsi file sebelum upload
#   RCLONE_REMOTE          mis. "b2:pmr-backup" → upload via rclone
#   RSYNC_TARGET           mis. "backup@vps2:/pmr" → upload via rsync (fallback)
# File lokal dihapus bila >24 jam; retensi offsite diatur di penyimpanan remote.
set -euo pipefail

MODE="${1:-daily}"
case "$MODE" in daily|weekly|monthly) ;; *)
  echo "Usage: $0 [daily|weekly|monthly]" >&2; exit 1 ;;
esac

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT_DIR"
BACKUP_DIR="${BACKUP_DIR:-$ROOT_DIR/backups}"
STAMP="$(date +%Y%m%d-%H%M%S)"
# Backup berisi data pribadi anggota: hanya pemilik yang boleh baca (file 600, folder 700).
umask 077
mkdir -p "$BACKUP_DIR"
chmod 700 "$BACKUP_DIR" 2>/dev/null || true

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

# --- 1. DB dump (custom format, lewat container postgres) ---
DB_FILE="$BACKUP_DIR/pmr-db-$MODE-$STAMP.dump"
log "pg_dump → $DB_FILE"
if ! compose exec -T postgres sh -c 'pg_dump -U "$POSTGRES_USER" -Fc "$POSTGRES_DB"' > "$DB_FILE"; then
  rm -f "$DB_FILE"
  echo "ERROR: pg_dump gagal — file backup dibuang." >&2
  exit 1
fi

# --- 2. File storage (upload publik: dokumentasi, foto, sertifikat) ---
STORAGE_FILE="$BACKUP_DIR/pmr-storage-$MODE-$STAMP.tar.gz"
log "tar storage → $STORAGE_FILE"
if ! compose exec -T app tar -czf - -C /var/www/html storage/app \
  | { umask 077 && cat > "$STORAGE_FILE"; }; then
  rm -f "$STORAGE_FILE"
  echo "ERROR: tar storage gagal — file backup dibuang." >&2
  exit 1
fi

FILES=("$DB_FILE" "$STORAGE_FILE")

# --- 3. Enkripsi (opsional, bila BACKUP_GPG_RECIPIENT di-set) ---
if [ -n "${BACKUP_GPG_RECIPIENT:-}" ] && command -v gpg >/dev/null 2>&1; then
  for f in "${FILES[@]}"; do
    log "gpg encrypt → $f.asc"
    gpg --batch --yes --trust-model always --recipient "$BACKUP_GPG_RECIPIENT" \
      --output "$f.asc" --encrypt "$f"
    rm -f "$f"
  done
fi

# --- 4. Upload offsite (rclone > rsync > warning) ---
shopt -s nullglob
for f in "$BACKUP_DIR"/pmr-"$MODE-$STAMP".* ; do
  if [ -n "${RCLONE_REMOTE:-}" ] && command -v rclone >/dev/null 2>&1; then
    log "rclone copy → $RCLONE_REMOTE/"
    rclone copy "$f" "$RCLONE_REMOTE/" --retries 3
  elif [ -n "${RSYNC_TARGET:-}" ]; then
    log "rsync → $RSYNC_TARGET/"
    rsync -av --partial "$f" "$RSYNC_TARGET/"
  else
    log "WARNING: tidak ada RCLONE_REMOTE/RSYNC_TARGET — file hanya lokal ($f)"
  fi
done

# --- 5. Retensi lokal: hapus file >24 jam (docs/09 §6.3) ---
find "$BACKUP_DIR" -maxdepth 1 -type f \( -name 'pmr-db-*' -o -name 'pmr-storage-*' \) \
  -mtime +1 -delete

log "done ($MODE)."
