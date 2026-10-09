# PMR Management System — Aturan Agent (AGENTS.md)

> File ini adalah **entry point** untuk AI coding agent (OpenCode).
> Dokumen lengkap ada di `docs/`. Jika terjadi konflik, ikuti `docs/` lalu tanyakan user.

## Stack
- Laravel 13 (PHP 8.3), Livewire, Blade, Tailwind CSS
- PostgreSQL, Redis
- Docker + Docker Compose, Nginx
- Testing: Pest/PHPUnit
- Deployment: Ubuntu LTS VPS + Cloudflare
- Scope: **1 sekolah** (tanpa multi-tenancy)
- Role: `admin`, `pengurus`, `anggota` (+ tamu/publik)

## Blueprint (baca sebelum kerja)
| Butuh | Baca |
|---|---|
| Ringkasan proyek | `docs/00_PROJECT_OVERVIEW.md` |
| Fitur & ruang lingkup | `docs/01_PRD.md`, `docs/02_REQUIREMENTS.md` |
| Struktur folder & layering | `docs/03_ARCHITECTURE.md` |
| Skema tabel / migration | `docs/04_DATABASE.md` |
| Role & Policy | `docs/05_AUTHORIZATION.md` |
| Halaman & menu | `docs/06_UI_UX.md` |
| Route & endpoint | `docs/07_API.md` |
| Keamanan & privasi | `docs/08_SECURITY.md` |
| Docker/VPS/backup | `docs/09_DEPLOYMENT.md` |
| Standar test & DoD | `docs/10_TESTING.md` |
| Aturan agent lengkap | `docs/11_OPEN_CODE_RULES.md` |
| Roadmap fase | `docs/12_ROADMAP.md` |
| Implementation plan (checklist progres) | `docs/13_IMPLEMENTATION_PLAN.md` |

## Rules (WAJIB)
1. Ikuti arsitektur & konvensi yang sudah ada.
2. Jangan memperkenalkan framework/library baru tanpa approval.
3. Jangan mengubah skema database tanpa migration.
4. Jangan pernah menghapus data di migration.
5. Setiap akses data sensitif harus melewati Policy (`authorize`).
6. Validasi semua input user via FormRequest.
7. File upload wajib divalidasi (MIME + ukuran).
8. **Jangan pernah menampilkan data internal anggota (NISN, alamat, no. HP, tgl lahir, data orang tua) di halaman/route publik.** Gunakan `Member::toPublicArray()`.
9. Jangan simpan secret/key di kode atau commit.
10. Tulis test untuk logika bisnis kritis.
11. Jalankan `php artisan test` sebelum menganggap tugas selesai.
12. Update `docs/` bila aturan/perilaku berubah.
13. Logika bisnis di `Action`/`Service`, bukan di Livewire component atau Blade.

## Development
- Gunakan Docker Compose; jangan instal dependensi langsung ke host.
- Environment dev = prod sebisa mungkin.
- Jangan mengubah produksi langsung; selalu via migration & deploy terstruktur.
- Seeder hanya untuk data development.
- Perintah umum:
  ```bash
  docker compose up -d
  docker compose exec app php artisan test
  docker compose exec app php artisan migrate
  docker compose exec app php artisan make:model X -mf
  docker compose exec app php artisan make:livewire Modul.Nama
  docker compose exec app php artisan make:policy XPolicY --model=X
  ```

## Definition of Done
- Fitur berjalan, test hijau, tidak ada perubahan skema tanpa migration, dokumentasi diperbarui.

## Prinsip
- **Monolit modular** — bukan microservices, bukan frontend/backend terpisah.
- **Dokumen = sumber kebenaran**, bukan percakapan sesaat.
- **Security & privasi sejak awal**, bukan tempel belakangan.
- **Fase per fase** — lihat `docs/12_ROADMAP.md`. Selesaikan Phase sebelum lompat.
