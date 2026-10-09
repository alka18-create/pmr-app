# 12 — Roadmap

Prinsip: **selesaikan satu fase sampai stabil (test hijau, jalan manual) sebelum lanjut.** Jangan bangun semua fitur sekaligus.

## Phase 1 — Foundation
> Tujuan: aplikasi bisa dijalankan, login, role berfungsi, setting & audit siap.

- [x] Inisialisasi project Laravel + Livewire + Tailwind + Docker Compose
- [x] Setup PostgreSQL + Redis + Nginx + worker (dev & prod compose)
- [x] Autentikasi (login, logout, session, rate limit)
- [x] Kolom `role` (admin/pengurus/anggota) + middleware role
- [x] Dashboard panel (layout, sidebar, dasbor kosong)
- [x] CRUD User (admin only)
- [x] Profil organisasi + Settings (key-value)
- [x] Audit log (service + trigger aksi kritis)
- [x] Seeder dasar + `PrivacyTest` & `AuthorizationTest` pertama
- [x] Dokumen `docs/` sudah final

**Keluaran:** aplikasi login, 3 role terpisah, audit & settings jalan. ✅ **Selesai — 61/61 test hijau.**

## Phase 2 — Anggota
> Status: ✅ **44/44** — selesai; test **101/101 hijau (432 assertions)**.

- [x] Model Member + migration (kolom publik vs internal)
- [x] `toPublicArray()` + `MemberPublicResource`
- [x] CRUD anggota (pengurus/admin) + FormRequest + Policy
- [x] Master: angkatan, kelas, jabatan (admin)
- [x] Riwayat jabatan
- [x] Filter/pencarian/pagination
- [x] Import Excel (template + validasi baris)
- [x] Export Excel
- [x] Halaman publik `/anggota` & `/anggota/{slug}` (data publik saja)
- [x] Test: PrivacyTest ketat, import/export

**Keluaran:** data anggota terpusat; publik hanya lihat data aman. ✅ **Selesai — 101/101 test hijau.**

## Phase 3 — Kegiatan & Absensi
> Status: ✅ **24/24 task** — selesai; test **125/125 hijau (519 assertions)**.

- [x] CRUD kegiatan + status + `is_public`
- [x] Program kerja tahunan
- [x] Peserta kegiatan + generate `qr_token`
- [x] Halaman detail kegiatan publik
- [x] Absensi manual (hadir/izin/sakit/alpa)
- [x] Check-in via QR (`GET /check-in/{token}`) + rate limit
- [x] Dokumentasi kegiatan (upload foto) → toggle publik
- [x] Rekap absensi per kegiatan/anggota (halaman dasar)
- [x] Test: absensi QR, otorisasi absensi

**Keluaran:** kegiatan & absensi digital menggantikan kertas. ✅ **Selesai — 125/125 test hijau.**

## Phase 4 — Content (Website Publik jadi)
> Status: ✅ **Test 156/156 hijau (648 assertions)** — sisa uji manual responsif & deploy staging

- [x] Beranda publik (hero, statistik, kegiatan mendatang, berita terbaru)
- [x] Halaman profil organisasi (sejarah, visi misi, struktur pengurus, 7 prinsip)
- [x] Artikel berita & pengumuman (CRUD + publish + SEO meta)
- [x] Galeri foto & video + kategori
- [x] Prestasi
- [x] Form kontak + rate limit + inbox
- [x] Sitemap + meta SEO + robots.txt
- [x] Test: hanya konten published tampil

**Keluaran:** website publik profesional siap dipakai organisasi.

## Phase 5 — Akademi
> Status: ✅ **Test 189/189 hijau (736 assertions)** — sisa uji manual siklus penuh

- [x] Materi (kategori, level, urutan, file PDF)
- [x] Quiz (pilihan ganda, timer, batas attempt, passing grade)
- [x] Auto-grading + nilai (`/panel/nilai`)
- [x] Sertifikat PDF + kode unik (queue `certificates`, dompdf + QR)
- [x] Halaman verifikasi publik `/verifikasi/{kode}`
- [x] Test hijau di mesin user (**189/189**)
- [ ] Uji manual siklus penuh (login anggota → materi → quiz → nilai → sertifikat → scan QR)

**Keluaran:** pelatihan & sertifikasi digital. ✅ **Test 189/189 hijau.**

## Phase 6 — Reporting
> Status: ✅ **Test hijau (seluruh suite passed)** — sisa uji manual PDF/Excel. Keputusan: chart CSS bar, export pola Fase 2, Dusk ditunda.

- [x] Rekap absensi (filter kegiatan/periode/anggota/kelas)
- [x] Statistik anggota (per kelas/angkatan/status/jabatan)
- [x] Statistik kegiatan (per bulan/jenis + rasio kehadiran)
- [x] Export PDF (laporan) + Excel + audit log
- [x] Dasbor dengan statistik + shortcut
- [~] (Opsional) Laravel Dusk E2E — ditunda ke Fase 7

**Keluaran:** pelaporan otomatis untuk pembina/sekolah. ✅ **Selesai — 210/210 test hijau (800 assertions).**

## Phase 7 — Advanced
- [x] PWA (installable, offline-lite) — manifest + SW + icon siap; uji install perangkat = aksi user
- [ ] REST API + Sanctum (bila dibutuhkan mobile) — **ditunda, keputusan user 2026-09-24**
- [x] Alumni management (bulk per angkatan + kembalikan ke aktif + setting publik)
- [x] Notification (email/in-app — nilai & sertifikat via mail queue, kegiatan in-app)
- [ ] CI/CD GitHub Actions + auto deploy — test+build **siap** (`ci.yml`); deploy manual-dispatch **siap** (`deploy.yml`); auto-on-main menyusul setelah 3× deploy sukses
- [ ] Backup otomatis offsite + uji restore berkala — skrip `scripts/backup-db.sh` **siap**; cron VPS + uji restore **menunggu aksi user**
- [x] Hardening keamanan lanjutan (CSP ketat awal via middleware; 2FA admin — **tidak jadi, keputusan user 2026-09-24**)
- [x] Prosedur penghapusan data pribadi

## Milestone & Kriteria
| Fase | Target Kriteria Selesai |
|---|---|
| 1 | Login 3 role jalan, audit & settings aktif |
| 2 | Import/export anggota jalan; PrivacyTest lolos |
| 3 | Absensi QR teruji; rekap absensi tersedia |
| 4 | Semua halaman publik live & SEO terpasang |
| 5 | Satu siklus quiz → sertifikat → verifikasi berhasil |
| 6 | Laporan PDF/Excel dihasilkan benar |
| 7 | Deploy otomatis & backup teruji restore — **kode siap (235/235 hijau); eksekusi VPS menunggu aksi user** |

## Aturan Perubahan Roadmap
- Penambahan fitur di luar daftar → masukkan ke fase terkait atau buat fase baru, **update dokumen ini dulu**, baru kode.
- Jangan melompat fase kecuali ada alasan mendesak (mis. kebutuhan mendadak sekolah) — catat alasannya di sini.
