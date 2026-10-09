# 04 — Database (ERD & Skema)

Engine: **PostgreSQL**. Semua perubahan skema **wajib via migration**. Jangan pernah menghapus data di migration.

## 1. ERD (ringkas)

```
users 1────1 members
users 1────* audit_logs
users 1────* notifications (opsional, Phase 7)

members *────1 batches (angkatan)
members *────1 classes  (kelas)
members *────* positions ──< member_position (riwayat jabatan)
members *────1 event_participants *────1 events
members *────1 attendance_records *────1 events
members *────1 quiz_attempts *────1 quizzes
members *────1 certificates

events *────1 event_documents (dokumentasi → gallery ketika publik)
events *────* gallery_items (opsional relasi)

articles (berita/pengumuman) *────1 users (penulis)
gallery_categories 1────* gallery_items
achievements (prestasi)
programs (program kerja)
materials (materi) ── categories via string/enum
quizzes 1────* quiz_questions 1────* quiz_options
quizzes 1────* quiz_attempts 1────* quiz_answers
certificates (kode unik, verifikasi publik)
settings (key-value)
audit_logs
```

## 2. Tabel Inti

### `users`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| name | varchar(100) | |
| email | varchar(155) unique | login |
| email_verified_at | timestamp nullable | |
| password | varchar(255) | hashed |
| role | varchar(20) | `admin`\|`pengurus`\|`anggota` |
| member_id | bigint FK nullable → members | hubungan dgn anggota |
| avatar_path | varchar nullable | |
| status | varchar(10) | `active`\|`inactive` |
| last_login_at | timestamp nullable | |
| remember_token, timestamps | | |

### `batches` (angkatan)
`id, name (contoh "Angkatan 2026"), year (int), timestamps`

### `classes` (kelas)
`id, name ("XI IPA 1"), level ("X"\|"XI"\|"XII"), year, timestamps`

### `positions` (jabatan)
`id, name, description, level (int: 1=ketua s.d. 9=anggota), timestamps`

### `members`
| Kolom | Tipe | Jenis |
|---|---|---|
| id | bigint PK | — |
| user_id | bigint FK nullable | relasi akun |
| nim / nis | varchar(20) nullable unique | **INTERNAL** |
| nisn | varchar(20) nullable unique | **INTERNAL** |
| full_name | varchar(100) | publik |
| slug | varchar(120) unique | publik |
| gender | enum laki\|perempuan | **INTERNAL** |
| birth_date | date nullable | **INTERNAL** |
| birth_place | varchar(100) nullable | **INTERNAL** |
| address | text nullable | **INTERNAL** |
| phone | varchar(20) nullable | **INTERNAL** |
| email_pribadi | varchar nullable | **INTERNAL** |
| parent_name | varchar nullable | **INTERNAL** |
| parent_phone | varchar nullable | **INTERNAL** |
| blood_type | varchar(3) nullable | **INTERNAL** |
| batch_id | FK batches | publik (angkatan) |
| class_id | FK classes | publik |
| position_id | FK positions | jabatan utama |
| photo_path | varchar nullable | publik |
| status | varchar(15) | `aktif`\|`alumni`\|`nonaktif` |
| joined_at | date | "Anggota sejak" |
| notes | text nullable | **INTERNAL** |
| timestamps, softDeletes | | |

> **PENTING:** Buat accessor/collection `Member::toPublicArray()` yang hanya mengembalikan field publik. Semua view publik WAJIB memakai ini.

### `member_position` (riwayat jabatan)
`id, member_id FK, position_id FK, start_date, end_date nullable, timestamps`

### `events` (kegiatan)
`id, title, slug, type (string: bakti_sosial|donor_darah|pelatihan|latihan_rutin|lainnya), description (text), location, starts_at, ends_at, cover_path, status (draft|published|selesai|batal), is_public (bool), max_participants (int nullable), created_by FK users, timestamps, softDeletes`

### `event_participants`
`id, event_id FK, member_id FK, role_in_event (peserta|panitia), qr_token (uuid unique), invited_at, timestamps, unique(event_id, member_id)`

### `attendance_records`
`id, event_id FK, member_id FK, status (hadir|izin|sakit|alpa|terlambat), checked_in_at, checked_in_by FK users nullable, method (manual|qr), notes, timestamps, unique(event_id, member_id)`

### `event_documents` (dokumentasi)
`id, event_id FK, file_path, file_type (image|video), caption, is_public (bool), uploaded_by, timestamps`

### `articles` (berita & pengumuman)
`id, title, slug unique, excerpt, content (text), category (berita|pengumuman|artikel), cover_path, meta_title, meta_description, status (draft|published), is_public (bool default true), published_at, author_id FK, views (int default 0), timestamps, softDeletes`

### `gallery_categories`
`id, name, slug unique, description, timestamps`

### `gallery_items`
`id, category_id FK nullable, title, description, file_path, file_type (image|video), video_url (nullable, YouTube), event_id FK nullable, is_public (bool), uploaded_by, taken_at date nullable, timestamps`

### `achievements` (prestasi)
`id, title, level (sekolah|kota|provinsi|nasional|internasional), category (lomba|penghargaan|akreditasi), year, description, attachment_path nullable, winner (string nullable, nama peserta), is_public (bool), timestamps`

### `programs` (program kerja)
`id, title, year, description, phase (Q1..Q4 atau bab), status (rencana|berjalan|selesai), owner_id FK users nullable, timestamps`

### `materials` (materi)
`id, title, slug, category (dasar_p3k|keorganisasian|kesehatan|kebencanaan|lainnya), content (text nullable), file_path nullable, level (mula|madya|wira), order (int), is_public (bool), uploaded_by, timestamps`
- **Keputusan Phase 5:** upload **PDF saja** (tanpa Markdown editor). Panel materi = login only (academy.view).
- Upload validasi: MIME `pdf`, maks 5 MB. Disimpan di disk `public` → `materials/{id}/`.

### `quizzes`
`id, title, slug, description, material_id FK nullable, time_limit_minutes int nullable, max_attempts int default 1, pass_score int default 70, is_active bool, starts_at nullable, ends_at nullable, created_by, timestamps`

### `quiz_questions`
`id, quiz_id FK, question (text), type (multiple_choice), score int default 10, order int, timestamps`

### `quiz_options`
`id, question_id FK, option_text, is_correct bool, order int, timestamps`

### `quiz_attempts`
`id, quiz_id FK, member_id FK, attempt_no int, started_at, finished_at, score int nullable (persen 0–100), status (running|finished|expired), ip varchar nullable, timestamps, unique(quiz_id, member_id, attempt_no)`
- Skor = `round(awarded/total*100)`; lulus bila `score >= quizzes.pass_score`.
- Timer server-side: expired bila `started_at + time_limit_minutes` lewat (dicek mount/submit).

### `quiz_answers`
`id, attempt_id FK, question_id FK, option_id FK nullable, is_correct bool, awarded_score int, timestamps, unique(attempt_id, question_id)`

### `certificates`
`id, code varchar(32) unique (kode verifikasi), member_id FK, source_type (quiz|event|training), source_id bigint nullable, title, issued_at date, file_path nullable (PDF), is_revoked bool default false, created_by, timestamps`
- **Trigger:** otomatis saat quiz lulus (queue `GenerateCertificate`); **manual** oleh pengurus (`IssueCertificate`) untuk kegiatan/pelatihan.
- Kode unik 32 char. PDF + QR → `GET /verifikasi/{code}`.
- Revoke menghapus file PDF & menandai `is_revoked=true` (verifikasi publik → tidak valid).

### `settings`
`id, key varchar unique, value json/text, group (general|contact|feature), timestamps`
- Key `org_principles` (group `general`): JSON list `[{title, body}]` — 7 Prinsip Dasar, dapat diubah admin via Pengaturan → Profil Organisasi; default redaksi resmi PMI (`App\Services\OrganizationPrinciples::defaults()`), fallback otomatis bila kosong/rusak.

### `audit_logs`
`id, user_id FK nullable, action varchar (created|updated|deleted|login|logout|export|import), model_type varchar, model_id bigint nullable, old_values json, new_values json, ip_address varchar, user_agent text, created_at`

### `messages` (form kontak publik) — Phase 4
`id, name, email, subject, body (text), status (unread|read|replied), ip_address nullable, timestamps`
- Index: `status`, `created_at`.
- Diisi oleh form `/kontak` (honeypot + rate limit).

### `notifications` — Phase 7
`id uuid primary, type varchar, notifiable_type, notifiable_id, data text, read_at nullable, timestamps`
- Fitur database notification bawaan Laravel (`Notifiable` di `User`).
- Type: `EventPublishedNotification` (kegiatan dipublish), `QuizResultPublishedNotification` (nilai terbit), `CertificateReadyNotification` (sertifikat siap).
- Penerima disaring `App\Services\Notifier`: user aktif; broadcast hanya member aktif.
- Email (queue `default`) hanya untuk nilai & sertifikat — kegiatan cukup in-app (tanpa email massal).

## 3. Indeks
- `users.email` unique, `users.role`
- `members.slug` unique, `members.nis`/`nisn` unique (partial index where not null)
- `members.class_id`, `members.batch_id`, `members.status`
- `events.starts_at`, `events.status`, `events.is_public`
- `attendance_records.event_id`, `attendance_records.member_id`
- `articles.status`, `articles.slug` unique, `articles.published_at`
- `quizzes.is_active`, `quizzes.slug` unique
- `quiz_attempts unique(quiz_id, member_id, attempt_no)`
- `certificates.code` unique
- `audit_logs.user_id`, `audit_logs.created_at`

## 4. Enums / Constraint
- Gunakan `varchar` + PHP enum (PHP 8.1 backed enum) untuk fleksibilitas, plus CHECK constraint opsional.
- `ON DELETE`: `softDeletes` untuk data anggota/konten; `RESTRICT` untuk relasi wajib.

## 5. Seeder (development)
- `RoleSeeder` (admin, pengurus, anggota)
- `UserSeeder` (1 admin demo: admin@pmr.test / password)
- `BatchSeeder`, `ClassSeeder`, `PositionSeeder`
- `MemberSeeder` (20 anggota contoh)
- `EventSeeder`, `ArticleSeeder`, `GallerySeeder`, `AchievementSeeder`, `AcademySeeder` (materi + quiz contoh)
- `SettingSeeder` (nama sekolah, kontak)

## 6. Aturan
1. Setiap perubahan tabel = file migration baru (tidak edit migration lama yang sudah jalan di prod).
2. Tidak ada `dropColumn` berisi data produksi tanpa persetujuan tertulis di PR.
3. Foreign key selalu dibuat, jangan andalkan referensi tanpa constraint.
4. Kolom money/nilai: gunakan tipe tepat (int untuk skor, numeric untuk rupiah bila ada).
5. Semua tabel punya `created_at`/`updated_at`.
