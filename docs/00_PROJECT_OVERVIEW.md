# 00 — Ringkasan Proyek (Project Overview)

## Nama Aplikasi
**PMR Management System** — Sistem Informasi Organisasi Palang Merah Remaja (PMR Wira) Tingkat SMA.

## Ringkasan
Aplikasi web monolitik Laravel untuk mengelola organisasi PMR di satu sekolah: data anggota, kegiatan & absensi, konten (berita/galeri/prestasi), materi akademi, serta sertifikat dengan verifikasi QR. Website publik menjadi wajah organisasi; panel internal menjadi sistem manajemen.

## Keputusan Utama

| Aspek | Keputusan |
|---|---|
| Arsitektur | Monolit modular Laravel (bukan microservices, bukan frontend/backend terpisah) |
| Scope | 1 sekolah (tanpa multi-tenancy) |
| Stack | Laravel 13 (PHP 8.3), Livewire, Blade, Tailwind CSS |

> **Revisi 23 Sep 2026:** Framework ditetapkan **Laravel 13** (bukan Laravel 11 sesuai rancangan awal) karena Laravel 11 sudah EOL (support berakhir Mar 2026) dan aplikasi ini menyimpan data siswa. Laravel 13 masih dalam dukungan penuh dan tetap kompatibel PHP 8.3. Keputusan disetujui user.
| Database | PostgreSQL |
| Cache/Queue | Redis |
| Server | Docker Compose → Nginx + App + Postgres + Redis + Worker |
| Deploy | Ubuntu LTS VPS di belakang Cloudflare |
| Role | 3 role: `admin`, `pengurus`, `anggota` (+ tamu/publik) |
| Bahasa UI & Dokumen | Bahasa Indonesia |

## Pilar Desain
1. **Konten publik vs data internal dipisah tegas.** Data pribadi siswa (NISN, alamat, no. HP, tgl lahir, data orang tua) tidak pernah tampil di halaman publik.
2. **Modul berbatas jelas** — setiap fitur punya folder, model, policy, dan route sendiri.
3. **Dokumen adalah sumber kebenaran** — OpenCode bekerja dari file `docs/`, bukan dari percakapan yang berubah-ubah.
4. **Migrasi = satu-satunya cara mengubah skema database.**
5. **Setiap akses sensitif melewati Policy.**

## Target Pengguna
| Pengguna | Kebutuhan |
|---|---|
| Publik (siswa/orang tua/guru) | Melihat profil, kegiatan, berita, galeri, prestasi, daftar anggota |
| Anggota PMR | Absensi, materi, quiz, sertifikat pribadi, profil sendiri |
| Pengurus | Kelola anggota, kegiatan, absensi, konten, laporan |
| Admin (Pembina/OSIS sekolah) | Kelola pengguna, role, settings, audit log, backup |

## Dokumen Blueprint
| File | Isi |
|---|---|
| `00_PROJECT_OVERVIEW.md` | Dokumen ini |
| `01_PRD.md` | Product Requirements Document |
| `02_REQUIREMENTS.md` | Requirement fungsional & non-fungsional |
| `03_ARCHITECTURE.md` | Arsitektur aplikasi & struktur folder modul |
| `04_DATABASE.md` | ERD dan definisi tabel |
| `05_AUTHORIZATION.md` | Role, permission, aturan akses |
| `06_UI_UX.md` | Peta halaman, menu, alur pengguna |
| `07_API.md` | Kontrak endpoint internal & rencana API |
| `08_SECURITY.md` | Aturan keamanan |
| `09_DEPLOYMENT.md` | Docker, VPS, Cloudflare, backup, CI/CD |
| `10_TESTING.md` | Strategi pengujian |
| `11_OPEN_CODE_RULES.md` | Aturan kerja AI coding agent |
| `12_ROADMAP.md` | Roadmap 7 fase |
| `13_IMPLEMENTATION_PLAN.md` | Rencana eksekusi per fase + checklist progres |
| `/AGENTS.md` | Aturan utama agent (root project) |
