# 09 — Deployment (Docker, VPS, Cloudflare, Backup, CI/CD)

## 1. Stack Infrastruktur
| Komponen | Pilihan |
|---|---|
| OS VPS | Ubuntu LTS (22.04/24.04) |
| Runtime | Docker + Docker Compose |
| Reverse proxy | Nginx (container) |
| App | PHP 8.3-FPM (container) |
| DB | PostgreSQL 16 (container) |
| Cache/Queue | Redis 7 (container) |
| Worker | container `worker` (queue:work) |
| Scheduler | container `scheduler` (schedule:work) |
| DNS/TLS/CDN | Cloudflare (Full Strict) |
| Repo | GitHub |

## 2. Docker Compose

### 2.1 Development (`docker-compose.yml`)
Service:
```
app      → build Dockerfile (php-fpm + composer + node)
nginx    → port 8080:80, volume public + config
postgres → port 5432 (localhost only), volume pgdata
redis    → port 6379 (localhost only), volume redisdata
worker   → same image as app, command queue:work
scheduler → same image as app, command schedule:work
```

Perintah awal:
```bash
cp .env.example .env
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app npm install && npm run build
# buka http://localhost:8080
```

### 2.2 Production (`docker-compose.prod.yml`)
- Tidak expose port DB/Redis ke luar.
- `APP_DEBUG=false`, `APP_ENV=production`.
- Volume khusus untuk `storage/` & `pgdata` (persist saat redeploy).
- Healthcheck pada app & postgres.
- Restart policy `unless-stopped`.

### 2.3 Dockerfile (ringkas)
```
FROM php:8.3-fpm
+ ext: pdo_pgsql, bcmath, gd, zip, intl, opcache
+ composer, node (build assets terpisah stage)
+ opcache aktif untuk prod
```

## 3. Nginx
- Serve `public/`, `try_files` ke `index.php`.
- **Lokasi `^~ /livewire-` wajib ada** sebelum regex statis — endpoint Livewire (`/livewire-{hash}/livewire.js`, `/update`, dll.) adalah route PHP dinamis; tanpa `^~`, regex `\.js` menangkapnya → 404 & JS mati (bug login Livewire).
- TLS dialihkan ke Cloudflare (origin cert atau Let's Encrypt).
- Header keamanan (lihat `08_SECURITY.md`).
- Rate limit dasar di nginx (opsional, utama di Cloudflare).
- Compress: gzip + brotli (Cloudflare sudah cache static).

## 4. Cloudflare
```
Pengunjung → Cloudflare (HTTPS, cache, WAF, rate limit) → VPS (origin)
```
Setting:
1. DNS: A record → IP VPS, proxy ON (orange cloud).
2. SSL/TLS: mode **Full (strict)**.
3. Always Use HTTPS: ON.
4. Cache: static assets (js/css/img) cache everything; HTML bypass.
5. Security: Bot Fight Mode, WAF basic rules, rate limit `/login` & `/check-in`.
6. Origin IP: jangan disebar (opsional: sembunyikan lewat config ketat).
7. Page Rules/Cache Rules: bypass cache untuk `/panel/*`, `/login`, `/check-in/*`.

## 5. Urutan Deploy (manual — V1)
```bash
ssh ke VPS
cd /opt/pmr-system
git pull origin main
docker compose -f docker-compose.prod.yml build app
docker compose -f docker-compose.prod.yml up -d
docker compose exec app composer install --no-dev --optimize-autoloader
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
docker compose exec app php artisan storage:link
docker compose exec app sh -c "npm install && npm run build"   # bila ada perubahan aset
docker compose restart nginx
```
> Jangan jalankan `migrate` sembarangan di produksi tanpa backup terlebih dahulu.

## 6. Backup
Prinsip: **backup harus berada di luar VPS yang sama.**

### 6.1 Jadwal
| Jenis | Frekuensi | Retensi |
|---|---|---|
| DB dump (pg_dump custom format) | harian 01:00 | 7 hari |
| DB dump | mingguan (Minggu) | 4 minggu |
| DB dump | bulanan (tgl 1) | 3 bulan |
| File storage (tar) | harian | 7 hari |

### 6.2 Lokasi
- Utama: object storage / S3-compatible (Backblaze B2 / Wasabi / Cloudflare R2).
- Alternatif: rsync ke VPS kedua atau komputer lokal定时.
- Enkripsi backup (age/gpg) sebelum kirim offsite.

### 6.3 Skrip (cron di VPS)
```
0 1 * * *  /opt/pmr-system/scripts/backup-db.sh daily
0 2 * * 0  /opt/pmr-system/scripts/backup-db.sh weekly
0 3 1 * *  /opt/pmr-system/scripts/backup-db.sh monthly
```
Script: `pg_dump -Fc` → tar → encrypt → upload → hapus file lokal >24 jam.
File compose dipilih otomatis: `docker-compose.prod.yml` bila `.env` punya `POSTGRES_PASSWORD`, selain itu `docker-compose.yml` — bisa dipaksa via env `PMR_COMPOSE_FILE`.

### 6.4 Uji Restore
Minimal **sebulan sekali** uji restore ke container lokal. Backup yang belum pernah diuji restore = bukan backup.

Prosedur (laptop/VPS, dari root proyek):
```bash
# 1. Buat backup (default output: <root proyek>/backups — bisa di-set via BACKUP_DIR)
./scripts/backup-db.sh daily

# 2. Restore ke database TERPISAH (aman, tidak menyentuh data utama)
./scripts/restore-db.sh backups/pmr-db-daily-<timestamp>.dump pmr_restore_test

# 3. Periksa sanity check hasil pg_restore (users/members/events/settings) lalu:
docker compose exec postgres dropdb -U pmr pmr_restore_test
```
- Restore ke DB utama (`./scripts/restore-db.sh file.dump`) = **destruktif**, minta ketik `YES`.
- Script memilih file compose otomatis (prod bila `.env` punya `POSTGRES_PASSWORD`); paksa dengan `PMR_COMPOSE_FILE=...`.
- Catat hasil uji di `docs/13_IMPLEMENTATION_PLAN.md` §7.6.

## 7. Monitoring & Log
- `docker compose logs -f` untuk investigasi.
- Laravel log: `storage/logs/laravel.log` (daily, 14 hari).
- Uptime: UptimeRobot/BetterStack cek `/health` endpoint.
- Opsional: Sentry untuk error produksi.
- Disk usage & memory dicek mingguan.

## 8. CI/CD (Phase 7 / setelah stabil)
```
OpenCode → Git → GitHub → Pull Request
   → GitHub Actions (test + build)
   → merge ke main
   → deploy job (SSH ke VPS, jalankan urutan deploy di atas)
```
GitHub Actions minimum:
1. `composer install`
2. `php artisan test` (butuh service Postgres di CI)
3. `npm run build`
4. Deploy (manual trigger dulu, lalu auto on main).

**Aturan:** jangan bangun pipeline kompleks sebelum deployment manual stabil (minimal 3 kali deploy sukses).

## 9. Environment Variables Wajib
```
APP_NAME, APP_ENV, APP_KEY, APP_DEBUG=false, APP_URL
DB_CONNECTION=pgsql, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
REDIS_HOST, REDIS_PASSWORD
SESSION_DRIVER=redis, CACHE_STORE=redis, QUEUE_CONNECTION=redis
MAIL_*, FILESYSTEM_DISK=public
SANCTUM_STATELESS_DOMAINS (bila Phase 7)
```

## 10. Checklist VPS Baru
- [ ] Ubuntu LTS, user non-root + sudo, SSH key
- [ ] ufw: 22/80/443 saja
- [ ] Docker + compose terpasang
- [ ] Clone repo ke `/opt/pmr-system`
- [ ] `.env` dibuat manual (tidak dari git)
- [ ] `docker compose up` sukses
- [ ] Domain diarahkan ke Cloudflare, SSL Full Strict
- [ ] Backup script + cron aktif, uji restore sekali
- [ ] Uptime monitoring aktif
