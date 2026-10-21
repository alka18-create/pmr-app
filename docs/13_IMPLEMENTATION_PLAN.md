# 13 — Implementation Plan

> **Cara pakai file ini:**
> 1. Kerjakan task berurutan dalam satu fase.
> 2. Centang `[x]` setiap task **setelah selesai + test hijau**.
> 3. Centang `## Definition of Done` fase hanya setelah seluruh task di dalamnya `[x]`.
> 4. **Jangan pindah fase** sebelum DoD fase sebelumnya tercentang.
> 5. Bila ada perubahan rencana → update `docs/` dulu, lalu file ini.
>
> Sumber kebenaran detail: `docs/00–12`. File ini = urutan eksekusi.

## Status Progres

| Fase | Status | Task Selesai |
|---|---|---|
| 1 — Foundation | ✅ Selesai | 64/64 |
| 2 — Anggota | ✅ Selesai | 44/44 |
| 3 — Kegiatan & Absensi | ✅ Selesai | 46/46 |
| 4 — Content (Publik) | ✅ Test hijau | 156/156 total |
| 5 — Akademi | ✅ Selesai | 189/189 test |
| 6 — Reporting | ✅ Selesai | 27/27 |
| 7 — Advanced | 🟡 Kode selesai, 244/244 hijau (sisa aksi user: uji PWA, cron backup, secrets GitHub, checklist rilis) | 22/39 |

> Update kolom Status: ⬜ belum · 🟡 berjalan · ✅ selesai.
> Update kolom Task: `n/total` setiap kali ada perubahan centang.

---

# FASE 1 — Foundation
**Goal:** Aplikasi jalan via Docker, login 3 role berfungsi, settings & audit siap.
**Referensi:** `docs/03_ARCHITECTURE.md`, `docs/05_AUTHORIZATION.md`, `docs/09_DEPLOYMENT.md`
**Status:** ✅ Selesai

### 1.1 Inisialisasi Project & Docker
- [x] Inisialisasi git repo + `.gitignore` Laravel (pastikan `.env`, `/vendor`, `/node_modules` ter-ignore)
- [x] Buat `Dockerfile` (php:8.3-fpm + ext pdo_pgsql, bcmath, gd, zip, intl, opcache, mbstring, pcntl, sockets + redis ext + composer + node 22)
- [x] Buat `docker-compose.yml` dev: service `app`, `nginx`, `postgres`, `redis`, `worker`
- [x] Buat `docker/nginx/default.conf` (root `public/`, fastcgi ke app:9000, header keamanan dasar)
- [x] Buat `docker/php/php.ini` (upload max, memory, opcache, timezone)
- [x] Buat `.env.example` (pgsql + redis untuk cache/session/queue, `FILESYSTEM_DISK=public`)
- [x] Buat `docker-compose.prod.yml` (tanpa port DB/Redis publik, healthcheck, restart unless-stopped, log rotation)
- [x] Jalankan `docker compose up -d` → sukses tanpa error
  - ✅ Terverifikasi di mesin user: semua service running/healthy, `GET http://localhost:8081` → **200**. Isu percobaan pertama sudah diperbaiki: port bentrok → `FORWARD_*_PORT` di `.env`; volume `vendor` root-owned → bind mount langsung (tanpa named volume).
- [x] Buat `README.md` singkat: cara menjalankan dev (`docs/09` §2.1)

### 1.2 Inisialisasi Laravel & Dependensi
- [x] Skeleton **Laravel 13** (`v13.10.1`) resmi diunduh dari GitHub ke root (pengganti `composer create-project`; keputusan versi: `docs/00_PROJECT_OVERVIEW.md`)
- [x] `docker compose run --rm app composer install` (37 paket, `vendor/` + `bootstrap/cache/` terisi, `composer.lock` terkunci)
- [ ] Install & konfigurasi Livewire (`docker compose exec app composer require livewire/livewire`)
- [x] Install Tailwind CSS + Vite (`npm install && npm run build` — `node_modules/` 77 paket, `public/build/manifest.json` ada; plugin `@tailwindcss/vite` + `@import 'tailwindcss'` di `app.css`)
- [x] Konfigurasi koneksi PostgreSQL (`config/database.php` default pgsql via `.env`)
- [x] Konfigurasi Redis untuk cache/session/queue (`CACHE_STORE`/`SESSION_DRIVER`/`QUEUE_CONNECTION=redis`)
- [x] `php artisan storage:link` (`public/storage` symlink terbentuk)
- [x] Pastikan halaman default tampil (`GET http://localhost:8081` → 200; port via `FORWARD_HTTP_PORT`)

### 1.3 Autentikasi
- [x] Buat scaffolding auth login (Blade + Livewire custom, **tanpa Breeze** — `app/Livewire/Auth/Login` + `layouts/guest`)
- [x] Route `GET|POST /login`, `POST /logout` di grup terpisah `routes/auth.php` (`docs/07` §2) + placeholder `GET /panel`
- [x] Rate limit login per email+IP: 5 gagal → lock 15 menit (`App\Actions\Auth\AttemptLogin`, `docs/08` §2.1)
- [x] Session config: `http_only` + `same_site=lax` (config bawaan), driver `redis`, `SESSION_SECURE_COOKIE=false` dev / `true` prod; redirect `guest`→`/panel`, `authed`→`/login` di `bootstrap/app.php`
- [ ] Halaman "lupa-password" + reset token (bisa tahap akhir fase)
- [x] Test: `AuthTest` — 12/12 hijau (38 assertions); 1 fix: `session()->regenerate()` via helper agar jalan di konteks `Livewire::test`

### 1.4 Role & Otorisasi Dasar
- [x] Migration tambah kolom `users.role` (`varchar(20)` default `anggota` + index; PHP enum `UserRole`)
- [x] Buat `RoleMiddleware` (`role:admin|pengurus|anggota`) + daftarkan alias — **fix**: split param pipe/komma (Laravel hanya split komma → pipe jadi1 string → salah403)
- [x] Definisikan Gate/Policy awal per `docs/05` §3: `Gate::before` (Admin lolos semua) + `access-pengurus-area`/`access-admin-area`
- [x] Route panel dibungkus middleware `auth` + `role` (`/panel`, `/panel/pengurus`, `/panel/admin`)
- [x] Redirect setelah login per role ke `/panel` (`redirectUsersTo('/panel')` + `intended`; diferensiasi konten dasbor → Fase 1.5)
- [x] Test: `AuthorizationTest` — 7/7 hijau (redirect tamu,403 per role, OK per role, gate bypass)

### 1.5 Layout Panel & Dasbor
- [x] Buat layout panel di `resources/views/components/layouts/panel.blade.php` (sidebar responsif + topbar + flash message; path `components/` agar bisa dipakai Blade `<x-layouts.panel>` & Livewire `#[Layout]` dari satu file)
- [x] Buat komponen reusable: `x-card`, `x-stat-card`, `x-alert`, `x-badge`, `x-empty-state` (`docs/06` §4)
- [x] Halaman `/panel` dasbor kosong (sapaan + statistik placeholder + empty state)
- [x] Sidebar menu conditional per permission (`docs/06` §2) — `@can('access-pengurus-area')` / `@can('access-admin-area')`
- [x] Halaman 403 & 404 custom (`docs/06` §8) + `APP_DEBUG=false` di phpunit — verifikasi: **25/25 test hijau (79 assertions)**; fix: `]` hilang di `x-alert` merge attributes

### 1.6 User Management (Admin)
- [x] Livewire `Users\UserIndex` (list, filter role/status, pagination)
- [x] Livewire `Users\UserCreate` / `UserEdit` + `UserRequest` (validasi)
- [x] Aksi nonaktifkan/aktifkan akun
- [x] Atur role user
- [x] Policy `UserPolicy` (`docs/05` §4) — admin only
- [x] Test: user CRUD hanya admin; validasi input — **37/37 hijau (129 assertions)**; fix: `void`+`return redirect`, FormRequest manual tanpa container, factory default `role`/`status`, toggle flash+redirect

### 1.7 Settings & Profil Organisasi
- [x] Migration tabel `settings` (key, value, group) — `docs/04`
- [x] Livewire `Settings\SettingIndex` (group general/contact/feature)
- [x] Helper `Setting::get('key', default)` + cache redis
- [x] Livewire `Settings\OrganizationProfile` (nama, sejarah, visi misi, logo, kontak, sosmed)
- [x] Policy `SettingPolicy` — admin only
- [x] Test: settings hanya bisa diubah admin; cache invalidation — **50/50 hijau (187 assertions)**; fix: cache `{found,value}` agar default berbeda tidak saling menimpa

### 1.8 Audit Log
- [x] Migration tabel `audit_logs` — `docs/04`
- [x] Model `AuditLog`
- [x] Service `AuditLogger::log(action, model, old, new)` (`docs/08` §6)
- [x] Panggil logger di aksi kritis user (create/update/delete user, login)
- [x] Livewire `AuditLog\AuditLogIndex` (filter user/action/tanggal) — admin only
- [x] Policy `AuditLogPolicy` — admin only
- [x] Test: aksi update user menghasilkan 1 baris audit dengan old/new values — **59/59 hijau (226 assertions)**; fix: filter test buat log langsung (pisah dari logging flow)

### 1.9 Seeder & Test Infrastruktur
- [x] `RoleSeeder` / `UserSeeder` (1 admin demo) — `docs/04` §5 — `admin@pmr.test` + `pengurus@` + `anggota@` / password
- [x] `BatchSeeder`, `ClassSeeder`, `PositionSeeder` — **+ migration & model** (dibutuhkan seeders & Fase 2)
- [x] `SettingSeeder` (nama sekolah, kontak)
- [x] Buat `tests/Feature/PrivacyTest.php` (template awal, diisi penuh di Fase 2)
- [x] `php artisan test` → hijau — **61/61 hijau (228 assertions)**

## ✅ Definition of Done — Fase 1
- [x] `docker compose up -d` jalan dari bersih (fresh clone → ikuti README) — terverifikasi 1.1 di mesin user
- [x] Login/logout 3 role berfungsi; route panel tanpa login redirect — `AuthTest` + `AuthorizationTest` hijau
- [x] Admin bisa CRUD user & settings; pengurus/anggota ditolak (403) — `UserManagementTest` + `SettingManagementTest` hijau
- [x] Audit log mencatat aksi user — `AuditLogTest` hijau
- [x] Seeder jalan; `php artisan test` hijau semua — `db:seed` idempoten + 61/61
- [x] Tidak ada secret di git; `.env.example` lengkap — `.env` untracked, `APP_KEY` kosong di contoh, 50 field

---

# FASE 2 — Anggota
**Goal:** Data anggota terpusat; publik hanya melihat data aman; import/export jalan.
**Referensi:** `docs/04_DATABASE.md` (members), `docs/02` (FR-MEM), `docs/05`, `docs/10` §3.1
**Status:** ✅ Selesai

### 2.1 Skema & Model
- [x] Migration tabel `members` (lengkap per `docs/04` §2 — kolom publik vs internal)
- [x] Migration relasi: `members.batch_id`, `members.class_id`, `members.position_id`, `members.user_id`
- [x] Migration tabel `member_position` (riwayat jabatan)
- [x] Model `Member` dengan `$fillable` ketat (tanpa `$guarded=[]`) — + `MemberPosition`, enum `MemberStatus`/`Gender`
- [x] Implementasi `Member::toPublicArray()` + `MemberPublicResource` (`docs/05` §5) — slug otomatis unik
- [x] Factory `MemberFactory` untuk test — + `BatchFactory`/`SchoolClassFactory`/`PositionFactory`

### 2.2 Policy & Akses
- [x] Policy `MemberPolicy`: `view`, `viewInternal`, `create`, `update`, `delete`, `import`, `export` (`docs/05` §4)
- [x] Daftarkan policy di `AuthServiceProvider` — auto-discovery Laravel (`Member` → `MemberPolicy`); `Gate::before` admin di `AppServiceProvider` tetap berlaku
- [x] `MemberRequest` (store/update) — validasi NISN unik, tgl lahir, dll
- [x] Test: anggota biasa TIDAK bisa akses data internal; pengurus/admin bisa — `MemberPolicyTest`

### 2.3 Master Data (Admin)
- [x] Livewire `Masters\BatchIndex` (CRUD angkatan) — + Action `SaveBatch`, `BatchRequest`
- [x] Livewire `Masters\ClassIndex` (CRUD kelas) — + Action `SaveSchoolClass`, `SchoolClassRequest`
- [x] Livewire `Masters\PositionIndex` (CRUD jabatan + level) — + Action `SavePosition`, `PositionRequest`
- [x] Policy/authorize admin-only untuk ketiganya — Gate `manage-master` (admin only) + route `role:admin`
- [x] Test: CRUD master hanya admin — `MasterDataTest` (7 test: akses, CRUD×3, validasi unique, FK dipakai)

### 2.4 CRUD Anggota (Internal)
- [x] Livewire `Members\MemberIndex` (list + filter kelas/angkatan/status + search + pagination — `docs/06` §4) — route `role:admin|pengurus`
- [x] Livewire `Members\MemberCreate` (form data publik + internal) — Action `CreateMember` + audit
- [x] Livewire `Members\MemberEdit` — Action `UpdateMember` + audit dirty keys
- [x] Detail `Members\MemberShow` (pakai `mount` authorize sesuai `docs/05` §6) — internal hanya `viewInternal`
- [x] Aksi nonaktifkan (`status`) — soft approach, tanpa hapus data — Action `SetMemberStatus`
- [x] Auto-generate `slug` unik — di model `booted` + `generateUniqueSlug`
- [x] Catat audit log create/update/delete member — filter NISN/NIS via `AuditLogger`
- [x] Test: filter/search benar; authorize 403/200 sesuai role — `MemberManagementTest` (9 test)

### 2.5 Import & Export Excel
- [x] **Konfirmasi user** untuk dependensi import/export (`maatwebsite/excel` atau native) — butuh approval per `AGENTS.md` rule 2 — **user pilih `maatwebsite/excel` ^3.1**
- [x] Halaman import: unduh template.xlsx — `MemberTemplateExport` + `downloadTemplate`
- [x] Upload + validasi baris (duplikat NISN, format tanggal, kelas tidak ada) — `FR-MEM-06` — Action `ImportMembers::preview`
- [x] Tampilkan ringkasan valid/error sebelum konfirmasi — Livewire `MemberImport`
- [x] Proses insert/update via Action `ImportMembers` (queue bila > 100 baris) — create-only; NISN unik ditolak; queue ditunda (skala ≤500)
- [x] Export anggota sesuai hak akses + audit log action=`export` — `ExportMembers` + `MembersExport` + `MemberExport` Livewire
- [x] Test: baris valid masuk, baris error dilaporkan, duplikat ditolak; export hanya role berhak — `MemberImportExportTest` (7/7; fix: baca xlsx via PhpSpreadsheet + materialize path Livewire, atribut validasi ID, `confirmImport` catch `DomainException`)

### 2.6 Halaman Publik Anggota
- [x] Route `GET /anggota` (filter kelas/angkatan/status, data publik saja)
- [x] Route `GET /anggota/{slug}` — profil publik: nama, kelas, jabatan, foto, angkatan, status (`docs/02` §C)
- [x] View WAJIB pakai `toPublicArray()` / `MemberPublicResource`
- [x] Sembunyikan alumni/nonaktif dari publik sesuai setting (`public_show_alumni` / `public_show_nonaktif`, group `feature`)
- [x] **PrivacyTest diperketat**: seed member dengan NISN/alamat/no HP unik → assert response publik TIDAK mengandung nilai itu (body & Livewire payload)
- [x] Test: `/anggota/{slug}` tidak pernah bocor field internal

### 2.7 Selesai Fase
- [x] Update `docs/04_DATABASE.md` bila ada deviasi skema — **tidak ada deviasi** (skema sesuai docs)
- [x] Jalankan seluruh `php artisan test` → hijau — **101/101 (432 assertions)**

## ✅ Definition of Done — Fase 2
- [x] Pengurus bisa tambah/ubah/nonaktifkan anggota; anggota biasa ditolak — `MemberManagementTest` + `MemberPolicyTest` hijau
- [x] Import template → upload → ringkasan → insert sukses; export jalan — `MemberImportExportTest` 7/7
- [x] `/anggota` & `/anggota/{slug}` tampil dengan hanya data publik
- [x] `PrivacyTest` penuh & hijau
- [x] Audit log terisi untuk aksi member — create/update/delete/import/export tercatat
- [x] Semua test hijau — **101/101 (432 assertions)**

---

# FASE 3 — Kegiatan & Absensi
**Goal:** Kegiatan & absensi digital (termasuk QR) menggantikan kertas.
**Referensi:** `docs/04` (events, participants, attendance), `docs/02` (FR-EVT), `docs/06` §3.1, `docs/07` §4.1
**Status:** ✅ Selesai — 125/125 test hijau (519 assertions)

### 3.1 Skema & Model
- [x] Migration `events` (lengkap per `docs/04`)
- [x] Migration `event_participants` (+ `qr_token` uuid unique, unique(event_id, member_id))
- [x] Migration `attendance_records` (unique(event_id, member_id), method manual|qr)
- [x] Migration `event_documents`
- [x] Migration `programs` (program kerja tahunan)
- [x] Model + Factory untuk kelima tabel
- [x] Action `GenerateParticipantQrToken` (uuid per peserta — auto saat creating)

### 3.2 Policy & Akses
- [x] `EventPolicy`: `view` (publik jika is_public & published), `create/update` (pengurus+), `delete` (admin / pembuat)
- [x] `AttendancePolicy`: `manage` (pengurus+), `viewOwn`, `checkInOwn` (`docs/05` §2)
- [x] Test: otorisasi event & absensi per matriks `docs/05` — `EventPolicyTest`

### 3.3 CRUD Kegiatan (Internal)
- [x] Livewire `Events\EventIndex` (list + filter jenis/status/tanggal)
- [x] Livewire `Events\EventCreate` / `EventEdit` + `EventRequest`
- [x] Upload poster (validasi MIME + max size per `docs/08` §2.3)
- [x] Aksi hapus event: admin atau pembuat (`EventPolicy::delete`)
- [x] Audit log aksi event
- [x] Test: create/update event role pengurus+; delete hanya admin/pembuat

### 3.4 Peserta & Absensi
- [x] Halaman detail event internal: kelola peserta (tambah/hapus)
- [x] Generate & tampilkan QR per peserta — qr_token uuid di `event_participants`
- [x] Absensi manual: hadir/izin/sakit/alpa (`AttendanceRequest`, Action `RecordAttendance`)
- [x] Halaman rekap absensi per event + filter — di detail event
- [x] Halaman rekap absensi per anggota — `Attendance\AttendanceRecap`
- [x] Test: absensi manual hanya pengurus; duplikat (event,member) update unique

### 3.5 Endpoint Check-in QR
- [x] Route `GET /check-in/{token}` (endpoint khusus, `docs/07` §4.1)
- [x] Halaman konfirmasi: sukses / sudah check-in / token salah / event berakhir
- [x] Rate limit 10/menit/IP (middleware `throttle:10,1` + RateLimiter)
- [x] Tulis `attendance_records` (method=qr, checked_in_by=null)
- [x] Test: token valid → hadir; token kedua → ditolak; token salah → 404; rate limit aktif

### 3.6 Dokumentasi Kegiatan
- [x] Upload foto/video di detail event (`event_documents`)
- [x] Toggle `is_public` per dokumen
- [x] Simpan file ke `storage/app/public/events/{id}/` dengan nama random (store)
- [x] Test: upload tervalidasi (MIME/ukuran)

### 3.7 Halaman Publik Kegiatan
- [x] Route `GET /kegiatan` (list published & is_public, urut waktu)
- [x] Route `GET /kegiatan/{slug}` (detail + dokumentasi publik)
- [x] Widget "kegiatan mendatang" untuk beranda (dipakai Fase 4)
- [x] Test: draft/batal tidak tampil di publik

### 3.8 Program Kerja
- [x] Livewire `Programs\ProgramIndex` (CRUD program kerja per tahun)
- [x] Policy pengurus+ (`access-pengurus-area` / Gate)
- [x] Test dasar CRUD — `ProgramManagementTest`

### 3.9 Selesai Fase
- [x] Update `docs/07_API.md` bila route menyimpang — disinkronkan (prefix `/panel/events`, `/panel/programs`, `/panel/attendance`)
- [x] Seluruh `php artisan test` → hijau — **125/125 (519 assertions)**

## ✅ Definition of Done — Fase 3
- [x] Pengurus bisa buat kegiatan + tambah peserta + absensi manual
- [x] QR check-in berfungsi (uji otomatis token valid/duplikat/salah/rate-limit; uji visual HP opsional di mesin user)
- [x] Rekap absensi per event & anggota akurat
- [x] Dokumentasi terupload & toggle publik bekerja
- [x] `/kegiatan` publik hanya menampilkan event published+public
- [x] Semua test hijau — **125/125 (519 assertions)**

---

# FASE 4 — Content (Website Publik Jadi)
**Goal:** Website publik profesional: beranda, profil, berita, galeri, prestasi, kontak, SEO.
**Referensi:** `docs/06_UI_UX.md`, `docs/04` (articles, gallery, achievements), `docs/02` (FR-CNT)
**Status:** ✅ Test hijau — 156/156 (648 assertions); sisa uji manual responsif/deploy

### 4.1 Skema & Model
- [x] Migration `articles` (slug, meta, status, is_public, published_at, author_id, views)
- [x] Migration `gallery_categories` + `gallery_items` (image/video/youtube_url, event_id relasi)
- [x] Migration `achievements`
- [x] Migration `messages` (form kontak)
- [x] Model + Factory ketiganya + Message
- [x] Sanitasi konten artikel (`{!! !!}` hanya untuk HTML ter-sanitize — `docs/08` §2.3) via `ContentSanitizer`

### 4.2 Policy & Akses
- [x] `ArticlePolicy`, `GalleryItemPolicy`, `AchievementPolicy`: `view` publik (published+is_public), `create/update/delete` pengurus+
- [x] Test: hanya konten published & is_public tampil di publik

### 4.3 CRUD Konten (Internal)
- [x] Livewire `Articles\ArticleIndex` + `ArticleCreate/Edit` (slug otomatis, meta SEO, cover upload)
- [x] Livewire `Gallery\GalleryIndex` + kategori CRUD (admin/pengurus)
- [x] Livewire `Achievements\AchievementIndex` + create/edit (level, tahun, lampiran, is_public)
- [x] Publish/unpublish + audit log
- [x] Test: validasi konten & authorize

### 4.4 Halaman Publik — Bukti Sosial & Navigasi
- [x] Layout publik `resources/views/components/layouts/public.blade.php` (header menu `docs/06` §1.1, footer kontak/sosmed)
- [x] Halaman **Beranda**: hero, statistik, kegiatan mendatang, berita terbaru, galeri preview, prestasi unggulan, CTA
- [x] Halaman **Profil** `/profil`: sejarah, visi misi, struktur pengurus, **7 Prinsip Dasar** + JSON-LD
- [x] Halaman **Berita** `/berita` + `/berita/{slug}` (pagination, meta SEO)
- [x] Halaman **Galeri** `/galeri` + `/galeri/{kategori}`
- [x] Halaman **Prestasi** `/prestasi` (filter tahun/tingkat)
- [x] Halaman **Kontak** `/kontak` (info dari settings + form pesan)
- [x] Form kontak: honeypot + rate limit; simpan ke `messages`

### 4.5 SEO & Perf Publik
- [x] Komponen `x-seo` (title, description, OG, canonical) dipakai layout publik
- [x] `GET /sitemap.xml` (berita, kegiatan, anggota publik)
- [x] `GET /robots.txt`
- [ ] Cache halaman publik (Redis, kecuali `/panel/*`, `/login`, `/check-in/*` — `docs/09` §4)
- [x] Structured data `Organization` di `/profil`
- [x] Test: sitemap ada; konten draft tidak ada di sitemap

### 4.6 Konfigurasi Kontak Masuk
- [x] Livewire `Messages\MessageIndex` (inbox form kontak) — pengurus+
- [x] Tandai sudah dibaca/balas (status sederhana)
- [x] Test dasar

### 4.7 Selesai Fase
- [ ] Uji manual responsif (640/768/1024/1280) semua halaman publik
- [ ] Aksesibilitas dasar: label form, kontras, alt text (`docs/06` §6)
- [x] Seluruh `php artisan test` → hijau — **156/156 (648 assertions)**

## ✅ Definition of Done — Fase 4
- [ ] Semua halaman publik (`docs/06` §1.1) tampil & responsif (uji manual user)
- [ ] Beranda menarik: hero + statistik + konten terbaru (uji manual user)
- [x] Hanya konten published+is_public yang tampil (test hijau)
- [x] SEO meta + sitemap aktif (test hijau)
- [x] Form kontak masuk ke inbox + rate-limited (test hijau)
- [ ] **Website siap dipublikasikan ke sekolah** (deploy staging manual per `docs/09` §5)

---

# FASE 5 — Akademi
**Goal:** Satu siklus materi → quiz → nilai → sertifikat → verifikasi publik.
**Referensi:** `docs/04` (materials, quizzes, certificates), `docs/02` (FR-ACA), `docs/07` §4.2
**Status:** ✅ Test hijau — 189/189 (736 assertions); sisa uji manual siklus penuh
**Keputusan user:** materi = **upload PDF saja** (tanpa MD editor); PDF library = **`barryvdh/laravel-dompdf`**; QR = **`endroid/qr-code`**.

### 5.1 Skema & Model
- [x] Migration `materials` (category, level, order, content/file, is_public)
- [x] Migration `quizzes` (time_limit, max_attempts, pass_score, active window)
- [x] Migration `quiz_questions` + `quiz_options` (is_correct, score, order)
- [x] Migration `quiz_attempts` (attempt_no, started/finished, score, status, unique(quiz,member,attempt_no))
- [x] Migration `quiz_answers` (option_id, is_correct, awarded_score)
- [x] Migration `certificates` (code unique, source_type/id, is_revoked, file_path)
- [x] Model + Factory semua tabel di atas

### 5.2 Policy & Akses
- [x] `MaterialPolicy`: `view`/`viewAny` (academy), `manage` (pengurus+)
- [x] `QuizPolicy`: `view`/`viewAny`, `manage`, `attempt` (anggota+, hanya saat aktif)
- [x] `CertificatePolicy`: `viewOwn`, `verify` (publik), `manage`
- [x] Test: anggota hanya lihat materi/quiz aktif; manage hanya pengurus+

### 5.3 Materi
- [x] Livewire `Materials\MaterialIndex` (list per kategori/level, admin/pengurus) — CRUD + upload PDF
- [x] Livewire `Materials\MaterialBrowse` (`/panel/materi` untuk anggota, download PDF)
- [x] Halaman `/panel/materi` untuk anggota + viewer materi (PDF link/embed)
- [x] Test: materi panel = login only; manage = pengurus+; wajib file PDF

### 5.4 Quiz Engine
- [x] Livewire `Quizzes\QuizIndex` (list quiz — anggota hanya `is_active`)
- [x] Livewire `Quizzes\QuizCreate/Edit` (soal + opsi + jawaban benar + skor)
- [x] Livewire `Quizzes\QuizTake` (timer server-side via `secondsRemaining`, simpan jawaban per soal, batas attempt, kunci saat submit)
- [x] Action `StartQuizAttempt`, `SubmitQuizAttempt` (grading + issue sertifikat bila lulus), `GradeQuizAttempt` (hanya finished)
- [x] Habisi attempt status `expired` bila waktu habis (cek saat mount/submit)
- [x] Livewire `Quizzes\ResultShow` (nilai, lulus/tidak, riwayat attempt)
- [x] Test: skor benar; attempt melebihi max_attempts ditolak; expired ditolak; grade only saat finished

### 5.5 Sertifikat + Verifikasi QR
- [x] Job `GenerateCertificate` — queue `certificates` (`docs/03` §7): kode unik 32 char, render PDF dompdf + QR endroid
- [x] Dependensi PDF: **`barryvdh/laravel-dompdf`** (approved); QR: **`endroid/qr-code`**
- [x] Trigger: otomatis saat quiz lulus DAN manual oleh pengurus untuk kegiatan (`IssueCertificate`)
- [x] Livewire `Certificates\CertificateIndex` (daftar, issue manual, revoke, regenerate, download) — pengurus+
- [x] Route publik `GET /verifikasi/{code?}` → halaman valid/dicabut/tidak ditemukan
- [x] Halaman `/panel/sertifikat` — unduh sertifikat milik sendiri
- [x] QR pada sertifikat mengarah ke `/verifikasi/{code}`
- [x] Test: lulus → sertifikat terbit; kode unik; verifikasi publik valid/invalid; revoke → tidak valid

### 5.6 Selesai Fase
- [x] **User:** `docker compose exec app composer require barryvdh/laravel-dompdf endroid/qr-code`
- [x] **User:** `docker compose exec app php artisan migrate`
- [x] **User:** `docker compose exec app php artisan test` → hijau — **189/189 (736 assertions)**
- [ ] Uji manual siklus penuh: login anggota → materi → quiz → nilai → sertifikat → scan QR verifikasi

## ✅ Definition of Done — Fase 5
- [x] Anggota bisa akses materi & mengerjakan quiz sesuai aturan attempt/waktu
- [x] Skor & kelulusan dihitung benar (test hijau)
- [x] Sertifikat PDF tergenerate via queue dengan kode unik
- [x] `/verifikasi/{kode}` publik bekerja (valid & tidak valid)
- [x] Semua test hijau — **189/189 (736 assertions)**

---

# FASE 6 — Reporting
**Goal:** Laporan & statistik otomatis, export PDF/Excel.
**Referensi:** `docs/02` (FR-RPT), `docs/07` §4.3, `docs/10` §3.4
**Status:** ✅ Test hijau di mesin user (seluruh suite passed); sisa uji manual PDF/Excel
**Keputusan user (Fase 6):** chart = **CSS bar** (tanpa dependensi baru); export = **pola Fase 2** (`Excel::download` sinkron + dompdf PDF, audit log `export`); E2E Dusk = **ditunda** (opsional).

### 6.1 Rekap Absensi
- [x] Halaman `Reports\AttendanceRecap` (filter event/periode/anggota/kelas)
- [x] Query/action `BuildAttendanceRecap` → `Services\Reports\AttendanceRecapBuilder` (logika di Service)
- [x] Export Excel rekap absensi + audit log action=`export`
- [x] **Konfirmasi user** dependensi PDF export (pakai `barryvdh/laravel-dompdf` dari Fase 5)
- [x] Export PDF rekap absensi (`Actions\Reports\ExportAttendanceRecap`)
- [x] Test: rekap per periode benar (hitung hadir/izin/sakit/alpa); export hanya pengurus+

### 6.2 Statistik Anggota
- [x] Stats: jumlah anggota per kelas, per angkatan, per status, per jabatan
- [x] `Services\Reports\MemberStatistics` + Livewire `Stats\MemberStats`
- [x] Test dasar perhitungan

### 6.3 Statistik Kegiatan
- [x] Stats: jumlah kegiatan per bulan/jenis/tahun; total peserta; rasio kehadiran
- [x] `Services\Reports\EventStatistics` + Livewire `Stats\EventStats`
- [x] Test dasar perhitungan

### 6.4 Dasbor Admin
- [x] Isi `/panel` (Livewire `Dashboard`) dengan stat cards + bar chart CSS + daftar kegiatan mendatang
- [x] Shortcut aksi cepat (tambah anggota, buat kegiatan)
- [x] Test: dasbor hanya tampil statistik internal untuk role berhak

### 6.5 Laporan Gabungan
- [x] Livewire `Reports\ReportIndex` (pilih jenis laporan → generate → preview → export)
- [x] Template PDF laporan (kop organisasi dari `settings.org_name`)
- [ ] Uji manual: hasil PDF/Excel sesuai data (user)

### 6.6 (Opsional) E2E Test — DITUNDA
- [~] Setup Laravel Dusk — **ditunda atas keputusan user** (opsional, rawan di Docker; bisa menyusul di Fase 7)
- [ ] E2E alur utama: login → buat kegiatan → absensi → cek rekap

### 6.7 Selesai Fase
- [x] Seluruh `php artisan test` → hijau (passed di mesin user)
- [x] Update `docs/02` / `docs/07` bila ada endpoint baru

## ✅ Definition of Done — Fase 6
- [x] Rekap absensi akurat & bisa diexport PDF + Excel
- [x] Statistik anggota & kegiatan tampil di dasbor
- [x] Export memicu audit log & hanya role berhak
- [x] Semua test hijau — **210/210 (800 assertions)**

> **Catatan pra-Fase 7:** ditambahkan service `scheduler` (`schedule:work`) + tugas `expire-stale-quiz-attempts`, dan dokumentasi akun demo (`admin@pmr.test` dkk) di README — aplikasi sudah bisa dipakai mandiri di laptop.

---

# FASE 7 — Advanced (setelah stabil)
**Goal:** PWA, alumni, notifikasi, CI/CD, backup otomatis, hardening.
**Referensi:** `docs/07` §6, `docs/08`, `docs/09` §8
**Status:** 🟡 Kode selesai — **244/244 test hijau (955 assertions)**; sisa item = aksi user (uji install PWA, cron VPS, uji restore, secrets GitHub, checklist rilis, push pertama)
**Keputusan user:** semua item **kecuali REST API+Sanctum**; **2FA admin tidak** (untuk saat ini); urutan **infra dulu → fitur**.

### 7.1 PWA
- [x] `manifest.json` + icon (PNG 180/192/512, cross merah) + service worker `public/sw.js` (pre-cache shell asset, navigasi network-first, **halaman panel tidak pernah di-cache** demi privasi)
- [x] Register SW hanya untuk halaman panel/anggota (script di `layouts/panel`; halaman publik/guest hanya pasang link manifest)
- [ ] Uji install di Android/iOS (**aksi user** — buka `/panel` di browser, menu "Tambahkan ke Layar Utama")

### 7.2 REST API — **DITUNDA (keputusan user)**
- [ ] ~~Install Sanctum~~ — tidak jadi di Fase 7; bisa ditambahkan bila ada kebutuhan mobile
- [ ] ~~Endpoint per `docs/07` §6~~
- [ ] ~~API Resource memakai payload publik/internal terpisah~~
- [ ] ~~Rate limit per token~~
- [ ] ~~Test API~~

### 7.3 Alumni
- [x] Bulk update status anggota → `alumni` (per angkatan) — `MarkBatchAsAlumni` + tombol "Tandai Alumni" (angkatan = filter terpilih)
- [x] Halaman publik alumni (opsional tampil/sembunyi via setting) — setting `public_show_alumni` + filter direktori (sudah ada, diuji `PrivacyTest`)
- [x] Fitur "kembalikan ke aktif" — toggle status + label khusus di daftar anggota
- [x] Test — `AlumniManagementTest` (4 test)

### 7.4 Notifikasi
- [x] Tabel `notifications` + Livewire badge + halaman notifikasi — migration, `NotificationsBadge` (header, poll 60s), `/panel/notifikasi` (scoped ke user sendiri)
- [x] Event→notification: kegiatan baru, nilai terbit, sertifikat siap — `Notifier` + trigger di `CreateEvent`/`UpdateEvent`, `SubmitQuizAttempt`, `IssueCertificate`(+`ForAttempt`)
- [x] Email via queue (`MAIL_*`) — nilai & sertifikat: database+mail; kegiatan: database saja (tanpa email massal)
- [x] Test: notification terbuat saat event dipublish — `NotificationTest` (8 test, termasuk non-duplikasi & scoping)

### 7.5 CI/CD
- [x] GitHub Actions workflow: composer install → `php artisan test` → `npm run build` (+ smoke migrate Postgres, `composer audit`) — `.github/workflows/ci.yml`
- [x] Badge status di README (placeholder `OWNER/REPO`, isi setelah push)
- [x] Deploy job manual-dispatch dulu (`docs/09` §8) — `.github/workflows/deploy.yml` (secrets: `SSH_HOST`, `SSH_USER`, `SSH_PRIVATE_KEY`); auto-on-main menyusul
- [ ] Minimal 3× deploy manual sukses sebelum auto-deploy

### 7.6 Backup Otomatis Offsite
- [x] `scripts/backup-db.sh` (pg_dump -Fc + tar storage → opsional gpg → rclone/rsync → hapus lokal >24 jam)
- [x] `scripts/restore-db.sh` (drop+create FORCE → pg_restore → sanity count; DB terpisah untuk uji, `pmr` butuh konfirmasi YES)
- [ ] Cron harian/mingguan/bulanan per `docs/09` §6.3 (**aksi user di VPS**)
- [x] **Uji restore sekali** ke container lokal (prosedur: `docs/09` §6.4 — `backup-db.sh` lalu `restore-db.sh <file> pmr_restore_test`):
  - [x] Restore test tanggal: 24/09/2026  hasil: ☑ sukses (sanity: users=4, members=0, events=2, settings=13; dropdb uji dibersihkan)
- [ ] Uptime monitoring (UptimeRobot) cek `/health` (endpoint sudah ada; tinggal daftar)
- [x] Endpoint `GET /health` (db + redis + storage check) + test — ✅ `HealthTest` 2 test

### 7.7 Hardening & Compliance
- [x] CSP header pertama via middleware `SecurityHeaders` (global, env-aware; longgar dulu) + header dasar nginx untuk file statis — diketatkan bertahap menyusul
- [x] `composer audit` / `npm audit` step di CI (critical) — jalankan lokal: `docker compose exec app composer audit`
- [x] 2FA admin — **TIDAK jadi (keputusan user)**
- [x] Prosedur penghapusan data pribadi siswa (permintaan orang tua/sekolah) + test — `PurgeMemberPersonalData` + tombol di detail anggota + `PrivacyDeletionTest`
- [ ] Checklist rilis `docs/08` §8 ditandatangani ✅ (**aksi user**, butuh kondisi VPS)
- [x] Retensi log (14 hari) aktif — `.env.example` → `LOG_CHANNEL=daily`, `LOG_DAILY_DAYS=14` (**ingat**: sinkronkan `.env` mesin yang sudah ada)

### 7.8 Selesai Fase
- [x] Full test suite hijau — **244/244 (955 assertions)** ✅ 2026-10-21 (termasuk uji tanpa `public/build`, agar CI tidak bergantung aset hasil build)
- [ ] Semua checklist `docs/09` §10 VPS baru terpenuhi (bila pindah server)

## ✅ Definition of Done — Fase 7
- [ ] PWA bisa diinstall (**uji perangkat = aksi user**); API — tidak jadi (ditunda)
- [ ] CI/CD hijau di setiap push (**menunggu push pertama ke GitHub**)
- [ ] Backup otomatis jalan + restore pernah diuji sukses (**aksi user: cron VPS + uji restore**)
- [ ] Checklist keamanan rilis lengkap (**aksi user: docs/08 §8**)
- [x] Semua test hijau — **244/244 (955 assertions)** ✅ 2026-10-21

---

## Panduan Update File Ini
1. **Centang task** saat selesai: `- [ ]` → `- [x]`.
2. **Update tabel Status Progres** di atas (Status + `n/total`).
3. Task baru muncul dari diskusi → tulis sebagai `[ ]` **di fase terkait**, dan bila mengubah ruang lingkup → update `docs/01–12` dulu.
4. Dependensi baru (library) → wajib **konfirmasi user** sebelum dipakai (AGENTS.md rule 2). Daftar dependensi yang sudah perlu konfirmasi ditandai di task terkait.
5. File ini bukan sumber kebenaran aturan — aturan tetap di `docs/00–12` dan `AGENTS.md`.
