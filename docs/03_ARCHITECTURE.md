# 03 — Arsitektur

## 1. Arsitektur Tinggi

```
INTERNET
   │
   ▼
Cloudflare  (DNS, HTTPS, caching, rate limit, WAF)
   │
   ▼
VPS (Ubuntu LTS + Docker Compose)
   │
   ├── Nginx  ──────────── reverse proxy, TLS, static files
   │      │
   │      ▼
   │   Laravel App (PHP-FPM)
   │      ├── Livewire  (interaktivitas halaman)
   │      ├── Blade     (templating)
   │      └── Tailwind  (styling)
   │      │
   │      ├── PostgreSQL  (data utama)
   │      ├── Redis       (cache + queue + session)
   │      └── Worker      (queue: export, sertifikat, email)
   │
   └── File Storage (volume Docker)  ── foto / PDF
```

Prinsip: **satu codebase, satu deployable**. Tanpa API terpisah, tanpa CORS, tanpa state management frontend.

## 2. Pilihan Teknis & Alasan

| Pilihan | Alasan | Alternatif yang ditolak |
|---|---|---|
| Monolit modular | 1 tim kecil, kurangi kompleksitas AI agent | Microservices, Next.js+API |
| Livewire | Interaktif tanpa SPA, tetap server-rendered | Vue/React SPA |
| PostgreSQL | Relasi kuat, laporan & JSONB untuk fleksibilitas | MySQL (boleh, tapi PG dipilih) |
| Redis | Cache + queue + session | Database-only (lambat) |
| Docker Compose | Environment dev = prod, mudah dipindah | Install manual di host |
| Role Enum + Gate/Policy | 3 role tetap (`admin`, `pengurus`, `anggota`) via kolom `users.role` + Gate/Policy bawaan Laravel, minim kompleksitas tabel tambahan & zero-dependency; arsitektur tetap siap di-upgrade ke Spatie Permission bila kelak butuh permission granular | Spatie Permission (overhead terlalu tinggi untuk kebutuhan 3 role tetap 1 sekolah di V1) |

## 3. Struktur Folder Project

```
pmr-system/
├── AGENTS.md                  ← aturan utama AI agent
├── README.md
├── docker-compose.yml
├── docker-compose.prod.yml
├── Dockerfile
├── .env.example
├── composer.json
├── package.json
├── docs/                      ← blueprint (source of truth)
│   ├── 00_PROJECT_OVERVIEW.md
│   ├── 01_PRD.md
│   ├── ... s.d 12_ROADMAP.md
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   ├── php/
│   │   └── php.ini
│   └── postgres/
│       └── init/ (opsional)
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── resources/
│   ├── css/
│   ├── js/
│   ├── views/
│   │   ├── layouts/
│   │   ├── public/            ← view situs publik
│   │   ├── livewire/          ← komponen Livewire
│   │   └── components/
│   └── lang/
├── routes/
│   ├── web.php
│   ├── auth.php
│   └── console.php
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Livewire/
│   ├── Models/
│   ├── Policies/
│   ├── Actions/               ← aksi bisnis 1 per file
│   ├── Services/              ← logika lintas modul
│   ├── Notifications/
│   ├── Exports/               ← Laravel Excel
│   └── Providers/
├── storage/
├── tests/
│   ├── Feature/
│   ├── Unit/
│   └── Pest.php atau TestCase.php
└── public/
```

### Catatan Modular
Kita **tidak** memakai package modul pihak ketiga (nwidart/laravel-modules) di V1 — cukup konvensi penamaan:

- Model: `App\Models\Member`, `App\Models\Event`, ...
- Route: prefix `/admin/...`, `/kegiatan/...`
- Livewire: `App\Livewire\Members\...`, `App\Livewire\Events\...`
- Policy: satu-to-one dengan model

Jika kelak modul tumbuh besar, migrasi ke folder `app/Modules/<Modul>/` boleh dibahas ulang (butuh approval).

## 4. Layering (aturan alur data)

```
Route → Policy (authorize) → FormRequest (validasi)
      → Livewire/Controller → Action/Service (bisnis)
      → Model (Eloquent) → PostgreSQL
      → View Blade (payload publik vs internal dibedakan)
```

Aturan:
1. **Tidak ada query Eloquent mentah di Blade.**
2. **Tidak ada logika bisnis di Livewire component** — pindahkan ke `Action`/`Service`.
3. **Tidak ada akses data sensitif tanpa Policy.**
4. **Validasi selalu di FormRequest**, bukan di dalam komponen.

## 5. Modul Aplikasi

| Modul | Tanggung Jawab | Fase |
|---|---|---|
| Foundation (Auth, User, Role, Settings, Audit) | Dasar platform | 1 |
| Members | Anggota, angkatan, kelas, jabatan, import/export | 2 |
| Events | Kegiatan, agenda, peserta, absensi QR, dokumentasi | 3 |
| Programs | Program kerja tahunan | 3 |
| Articles | Berita, pengumuman | 4 |
| Gallery | Foto, video, kategori | 4 |
| Achievements | Prestasi | 4 |
| Materials | Materi pelatihan | 5 |
| Quizzes | Quiz & nilai | 5 |
| Certificates | Sertifikat & verifikasi QR | 5 |
| Reports | Laporan & statistik | 6 |
| Settings | Pengaturan organisasi & sistem | 1 |

## 6. Pengelolaan File (Storage)

- V1: `public` disk lokal di volume Docker (`/var/www/html/storage/app/public`), symlink `public:storage`.
- Struktur: `members/{id}/`, `events/{id}/`, `gallery/`, `certificates/`.
- Upload selalu divalidasi: MIME, ukuran maks (foto 2MB, PDF 5MB), dimensi.
- Opsional kemudian: Laravel S3-compatible (Wasabi/Backblaze) untuk offsite.

## 7. Queue & Job

| Job | Fase | Queue |
|---|---|---|
| ExportAnggota (Excel) | 2 | `exports` |
| ExportLaporan | 6 | `exports` |
| GenerateSertifikat (PDF) | 5 | `certificates` |
| KirimEmailNotifikasi | 7 | `default` |

Worker berjalan di container `worker` dengan image yang sama, command: `php artisan queue:work redis --queue=exports,certificates,default`.

Scheduler berjalan di container `scheduler` (image sama), command: `php artisan schedule:work`. Tugas terjadwal saat ini: `expire-stale-quiz-attempts` (tiap 5 menit) menandai attempt quiz yang lewat batas waktu sebagai `expired`.

## 8. Environment

| | Development | Production |
|---|---|---|
| Compose | `docker-compose.yml` | `docker-compose.prod.yml` |
| APP_DEBUG | true | false |
| APP_ENV | local | production |
| Cache/Session/Queue | redis | redis |
| HTTPS | tidak (port 8080) | Cloudflare + Nginx |
| Log | single/debug | daily |
