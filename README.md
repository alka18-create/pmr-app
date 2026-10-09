# PMR Management System

[![CI](https://github.com/alka18-create/pmr-app/actions/workflows/ci.yml/badge.svg)](https://github.com/alka18-create/pmr-app/actions/workflows/ci.yml)

Sistem informasi organisasi **Palang Merah Remaja (PMR) tingkat SMA** — monolitik Laravel + Livewire + PostgreSQL.

> **Aturan agent:** lihat [`AGENTS.md`](AGENTS.md).
> **Blueprint proyek:** lihat [`docs/`](docs/) — mulai dari [`docs/00_PROJECT_OVERVIEW.md`](docs/00_PROJECT_OVERVIEW.md).
> **Progres pengerjaan:** [`docs/13_IMPLEMENTATION_PLAN.md`](docs/13_IMPLEMENTATION_PLAN.md).

## Stack

| Komponen | Teknologi |
|---|---|
| Framework | Laravel 13 (PHP 8.3) + Livewire + Blade |
| Styling | Tailwind CSS + Vite |
| Database | PostgreSQL 16 |
| Cache / Session / Queue | Redis 7 |
| Server | Docker Compose → Nginx + PHP-FPM + Worker |

## Prasyarat

- Docker Engine + Docker Compose v2
- Git

## Menjalankan (Development)

```bash
# 1. Salin environment (lalu cek UID/GID & port — lihat catatan di bawah)
cp .env.example .env

# 2. Build image & jalankan database dulu
docker compose build
docker compose up -d postgres redis

# 3. Install dependensi PHP (di dalam container, bukan di host)
docker compose run --rm app composer install

# 4. Buat application key
docker compose run --rm app php artisan key:generate

# 5. Jalankan seluruh service (app, nginx, worker)
docker compose up -d

# 6. Install dependensi JS + build aset
docker compose run --rm app sh -c "npm install && npm run build"

# 7. Migrasi database + seeder + symlink storage
docker compose run --rm app php artisan migrate --seed
docker compose run --rm app php artisan storage:link
```

Buka **http://localhost:8080** (atau port `FORWARD_HTTP_PORT` bila diatur).

### Akun Demo (hasil `migrate --seed`)

Semua akun memakai password **`password`** (ganti setelah login pertama):

| Role | Email |
|---|---|
| Admin | `admin@pmr.test` |
| Pengurus | `pengurus@pmr.test` |
| Anggota | `anggota@pmr.test` |

Seeder (`UserSeeder` + `RoleSeeder`) bersifat idempoten (`firstOrCreate`), jadi aman dijalankan berulang.
Akun demo pengurus & anggota otomatis ditautkan ke profil anggota (`DemoSeeder`, local saja) agar alur quiz/absensi bisa dicoba langsung.
DB yang sudah di-seed sebelum perubahan ini: jalankan `docker compose exec app php artisan db:seed --class=DemoSeeder` sekali.

### Catatan `.env`

```bash
id -u                                   # hasilnya isi ke UID= di .env (default 1000)
id -g                                   # hasilnya isi ke GID= di .env (default 1000)

# Bila port default bentrok dengan container/service lain di mesin Anda:
# cek dulu dengan:  docker ps
# lalu aktifkan & sesuaikan di .env:
FORWARD_HTTP_PORT=8081   # ganti 8080
FORWARD_DB_PORT=15432    # ganti 5432
FORWARD_REDIS_PORT=16379 # ganti 6379
```

Port `FORWARD_*` hanya port di **host** — port di dalam jaringan compose selalu `80`/`5432`/`6379`, jadi `DB_HOST=postgres` tidak perlu diubah.

## Perintah Umum

```bash
docker compose exec app php artisan test        # jalankan seluruh test
docker compose exec app php artisan migrate     # jalankan migration
docker compose exec app php artisan tinker      # REPL
docker compose logs -f worker                   # log queue worker
docker compose down                             # hentikan semua service
docker compose up -d --build                    # rebuild & jalankan lagi
```

## Troubleshooting

**Port sudah dipakai** (`Bind for ... failed: port is already allocated`):
atur `FORWARD_*_PORT` di `.env` ke port bebas, lalu `docker compose up -d`.

**`vendor` tidak bisa ditulis / permission**:
`vendor` dan `node_modules` ditulis langsung ke bind mount (milik user `UID`).
Bersihkan sisa volume lama sekali saja:
```bash
docker compose down
docker volume rm pmr-system_vendor pmr-system_node_modules  # abaikan bila tidak ada
```

**Worker error `vendor/autoload.php`**:
`composer install` belum selesai — jalankan langkah 3, lalu `docker compose restart worker`.

## Service Docker

| Service | Fungsi | Port (host) |
|---|---|---|
| `nginx` | Reverse proxy + serve `public/` | `${FORWARD_HTTP_PORT:-8080}` |
| `app` | PHP-FPM (Laravel) | — |
| `worker` | Laravel queue worker (`queue:work`) | — |
| `scheduler` | Laravel scheduler (`schedule:work`) | — |
| `postgres` | PostgreSQL 16 | `127.0.0.1:${FORWARD_DB_PORT:-5432}` |
| `redis` | Redis 7 | `127.0.0.1:${FORWARD_REDIS_PORT:-6379}` |

Postgres & Redis sengaja **hanya diakses dari dalam jaringan compose** (via host `postgres` dan `redis`).

## Struktur Dokumen

```
AGENTS.md                  Aturan utama AI coding agent
docs/00_PROJECT_OVERVIEW   Ringkasan proyek
docs/01_PRD                Product requirements
docs/02_REQUIREMENTS       FR & NFR
docs/03_ARCHITECTURE       Arsitektur & struktur folder
docs/04_DATABASE           ERD & skema tabel
docs/05_AUTHORIZATION      Role & permission
docs/06_UI_UX              Halaman & menu
docs/07_API                Route & endpoint
docs/08_SECURITY           Aturan keamanan
docs/09_DEPLOYMENT         Docker, VPS, Cloudflare, backup
docs/10_TESTING            Strategi test & DoD
docs/11_OPEN_CODE_RULES    Aturan agent lengkap
docs/12_ROADMAP            Roadmap 7 fase
docs/13_IMPLEMENTATION_PLAN Checklist progres per fase
```

## Produksi

Deployment ke VPS + Cloudflare: ikuti [`docs/09_DEPLOYMENT.md`](docs/09_DEPLOYMENT.md) (compose produksi: `docker-compose.prod.yml`).
