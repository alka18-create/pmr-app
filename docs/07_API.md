# 07 — Endpoint & Kontrak Internal

## Status
V1 = **monolitik Livewire**, jadi mayoritas interaksi via komponen Livewire, bukan REST API. Dokumen ini mencatat:
1. Route web penting (public & panel).
2. Endpoint minimal yang tetap dibutuhkan (check-in QR, verifikasi, export).
3. Rencana REST API (Phase 7) bila dibutuhkan PWA/mobile.

## 1. Route Web Publik
```
GET  /                       beranda ✅
GET  /profil                  profil organisasi ✅
GET  /kegiatan                list (published & is_public)          ✅
GET  /kegiatan/{slug}         detail + dokumentasi publik           ✅
GET  /berita                  list (status=published & is_public) ✅
GET  /berita/{slug}           detail + views + sanitasi HTML      ✅
GET  /galeri                  list item publik                    ✅
GET  /galeri/{categorySlug}   per kategori                        ✅
GET  /prestasi                list prestasi publik (filter tahun/tingkat) ✅
GET  /anggota                 list data publik (filter: kelas, angkatan, status) ✅
GET  /anggota/{slug}          profil publik                         ✅
GET  /verifikasi/{code?}      verifikasi sertifikat (rate limit 30/menit) ✅
GET  /kontak                  form pesan (honeypot + rate limit) ✅
GET  /sitemap.xml             sitemap (berita, kegiatan, anggota) ✅
GET  /robots.txt              robots + sitemap URL ✅
```

### 1a. Route Panel Konten (auth + role:admin|pengurus)
```
GET  /panel/articles          list + filter ✅
GET  /panel/articles/create   buat artikel ✅
GET  /panel/articles/{id}/edit edit + publish/unpublish ✅
GET  /panel/gallery           CRUD item + kategori ✅
GET  /panel/achievements      CRUD prestasi ✅
GET  /panel/messages          inbox form kontak ✅
```

### 1b. Route Panel Akademi (Phase 5) ✅
```
# auth — anggota+ (academy.view)
GET  /panel/materi            daftar materi (PDF embed/download)
GET  /panel/quiz              daftar quiz (anggota: hanya is_active)
GET  /panel/quiz/{quiz}       kerjakan quiz (QuizTake — timer server-side)
GET  /panel/nilai             nilai & riwayat attempt milik sendiri
GET  /panel/sertifikat        sertifikat milik sendiri (unduh PDF)

# auth + role:admin|pengurus (academy.manage)
GET  /panel/materi/manage     CRUD materi (upload PDF)
GET  /panel/quiz/create       buat quiz + soal PG
GET  /panel/quiz/{quiz}/edit  edit quiz (soal dikunci bila sudah ada attempt)
GET  /panel/sertifikat/manage terbit/cabut/regenerate sertifikat
```

### Livewire actions akademi (bukan route HTTP terpisah)
```
# MaterialIndex: save, startEdit, delete
# QuizCreate/QuizEdit: save, addQuestion, removeQuestion, addOption, setCorrect
# QuizTake: submit (GradeQuizAttempt + issue sertifikat bila lulus)
# CertificateIndex: issue, revoke, regenerate, download
# MyCertificates: download
# CertificateVerify (publik): search
```

## 2. Route Autentikasi
```
GET  /login                   ✅
POST /login                   (rate limited) ✅
POST /logout                  ✅
GET  /lupa-password           ✅
POST /lupa-password           (rate limited) ✅
GET  /reset-password/{token}  ✅
POST /reset-password          ✅
GET  /panel                   (redirect per role) ✅
```

## 3. Route Panel (auth required)
Semua prefix `/panel`, middleware `auth` + authorize per Policy / role middleware.

```
GET  /panel                   ✅
GET  /panel/profil            ✅
PUT  /panel/profil            ✅

# auth + role:admin|pengurus (atau Gate access-pengurus-area)
GET  /panel/members                               ✅
GET  /panel/members/create                        ✅
GET  /panel/members/import                        ✅
GET  /panel/members/export                        ✅
GET  /panel/members/{member}                      ✅
GET  /panel/members/{member}/edit                 ✅

GET  /panel/events                                ✅ list + filter
GET  /panel/events/create                         ✅ + poster upload
GET  /panel/events/{event}                        ✅ peserta, absensi, dokumentasi, QR link
GET  /panel/events/{event}/edit                   ✅
GET  /panel/programs                              ✅ CRUD program kerja
GET  /panel/attendance                            ✅ rekap absensi per event/anggota

# auth + role:admin|pengurus (akademi manage)
GET  /panel/materi/manage                         ✅
GET  /panel/quiz/create                           ✅
GET  /panel/quiz/{quiz}/edit                      ✅
GET  /panel/sertifikat/manage                     ✅

# auth (akademi anggota+)
GET  /panel/materi                                ✅
GET  /panel/quiz                                  ✅
GET  /panel/quiz/{quiz}                           ✅ kerjakan
GET  /panel/nilai                                 ✅
GET  /panel/sertifikat                            ✅ unduh milik sendiri

# Livewire actions (bukan route HTTP terpisah)
# - EventShow: addParticipant, addParticipantsByClass, removeParticipant,
#   saveAttendance, uploadDocument, delete
# - Check-in peserta: GET /check-in/{qr_token} (konfirmasi) + POST /check-in/{qr_token} (catat) — public

# admin only (role:admin)
GET  /panel/admin/users
GET  /panel/admin/users/create
GET  /panel/admin/users/{user}/edit
GET  /panel/admin/settings
GET  /panel/admin/settings/organization
GET  /panel/admin/audit-log
GET  /panel/admin/master/batches|classes|positions

# Phase 6 ✅
GET       /panel/laporan                          ✅ pilih jenis laporan
GET       /panel/laporan/absensi                  ✅ rekap absensi + Livewire action exportExcel/exportPdf
GET       /panel/statistik/anggota                ✅ statistik anggota
GET       /panel/statistik/kegiatan               ✅ statistik kegiatan
# Dasbor /panel diisi stat cards + CSS bar chart + shortcut (Phase 6)

# Phase 7 ✅
GET       /panel/notifikasi                       ✅ daftar notifikasi sendiri (auth, semua role)
# Livewire: NotificationsIndex (markRead, markAllRead), NotificationsBadge (poll 60s, di header panel)
# Livewire: MemberIndex.markBatchAsAlumni (bulk alumni per angkatan terpilih)
# Livewire: MemberShow.purgePersonalData (penghapusan data pribadi, docs/08 §3.3)
```

Catatan deviasi dari rencana lama:
- Kegiatan memakai prefix `/panel/events` (bukan `/panel/admin/kegiatan`) — konsisten dengan Livewire namespace `Events\`.
- Anggota memakai `/panel/members` (bukan `/panel/admin/anggota`).
- Program & rekap absensi di level pengurus (bukan admin-only).
- Akademi memakai `/panel/materi` + `/panel/materi/manage` (bukan `/panel/admin/materi`) — konsisten dengan Articles/Gallery.

## 4. Endpoint Khusus (bukan halaman Livewire)

### 4.1 Check-in QR
```
GET  /check-in/{token}   halaman konfirmasi (read-only, tidak mencatat apa pun)
POST /check-in/{token}   mencatat kehadiran (CSRF)
```
- Token = `event_participants.qr_token` (UUID v4 acak, 122 bit).
- GET hanya menampilkan nama kegiatan + anggota dan tombol "Catat Kehadiran Saya", supaya prefetch / pratinjau tautan tidak mencatat absensi tanpa disengaja.
- POST mengembalikan halaman "Berhasil check-in" / error (sudah check-in 409, token salah 404, kegiatan tidak aktif/berakhir 422).
- Rate limited 30 percobaan/menit/IP (GET + POST dihitung; longgar karena satu kelas bisa berbagi IP Wi-Fi sekolah). Konstanta: `CheckInController::MAX_ATTEMPTS_PER_MINUTE`.
- POST mencatat `attendance_records` (method=qr, checked_in_at=now).

### 4.2 Verifikasi Sertifikat
```
GET /verifikasi/{code?}
```
- Publik. Rate limited 30/menit/IP.
- Response: valid (nama, judul, tanggal, kode) / dicabut / tidak ditemukan.
- Tidak membocorkan data internal tambahan (NISN, alamat, dll).

### 4.3 Export (Butuh auth + permission)
```
GET /panel/members/export
GET /panel/laporan/absensi        → Livewire action exportExcel (.xlsx) / exportPdf (.pdf)
```
- Export anggota: Livewire action `MemberExport::download` → Action `ExportMembers` mengembalikan response `Excel::download()` (wajib di-`return` sampai ke komponen agar file terkirim). Rekap absensi: Excel via Livewire, PDF via `AttendanceReportPdfController` (`GET /panel/laporan/absensi/pdf`, dompdf).
- Selalu dicatat ke `audit_log` action=`export` (resource: `members` / `attendance_recap`).

### 4.4 Health check (publik, Phase 7 ✅)
```
GET /health
```
- Publik tanpa auth — dipakai UptimeRobot/BetterStack (docs/09 §7).
- Response JSON: `{ "status": "ok"|"failed", "checks": { "db", "cache", "storage" } }`.
- `200` bila semua `ok`, `503` bila ada komponen `failed` (probe `select 1`, `Redis::ping()`, `is_writable(storage)`).

## 5. Format Respons Konten Publik
Untuk menjaga konsistensi & keamanan, model publik dibungkus resource:

```php
MemberPublicResource::make($member)
// { slug, full_name, kelas, angkatan, jabatan, photo_url, status, joined_at }
```
**Jangan pernah** return `$member->toArray()` mentah di route publik.

## 6. REST API (Phase 7 — **DITUNDA, keputusan user 2026-09-24**)
Bila dibutuhkan PWA/mobile:
```
POST /api/v1/login
GET  /api/v1/me
GET  /api/v1/events
GET  /api/v1/events/{id}
POST /api/v1/events/{id}/check-in
GET  /api/v1/materi
POST /api/v1/quizzes/{id}/attempts
GET  /api/v1/certificates
```
- Auth: Laravel Sanctum (personal access token).
- Versioning: `/api/v1/`.
- Rate limit per token.
- **Tidak ada rencana ini di V1** — jangan buat endpoint ini tanpa approval.

## 7. Aturan Route
1. Route publik vs panel dipisah file/group.
2. Route id selalu `{id}` numeric atau `{slug}` — konsisten.
3. Semua route panel wajib middleware `auth` minimal.
4. Route admin wajib `can:` / Policy, jangan hanya cek role manual di view.
5. Model binding implicit pakai `{slug}` untuk konten publik (SEO).
