# 06 — UI/UX

Prinsip: **mobile-first**, sederhana, cepat, aksesibel, Bahasa Indonesia, identitas visual PMR (merah `#E31837` khas PMI + putih + abu gelap).

**Design token** (`resources/css/app.css`, Tailwind v4 `@theme`): skala `brand-*` diselaraskan di `#E31837` (`brand-600`), font `Plus Jakarta Sans` via Google Fonts (diizinkan eksplisit di CSP, docs/08 §2.4). Kode baru memakai `brand-*`; `red-*` lama dibiarkan hingga sapuan panel.

## 1. Peta Situs

### 1.1 Publik (tanpa login)
```
/                       Beranda
/profil                 Tentang PMR (sejarah, visi misi, struktur, 7 prinsip)
/kegiatan                Agenda & galeri kegiatan
/kegiatan/{slug}         Detail kegiatan
/berita                  Daftar berita
/berita/{slug}           Detail berita
/galeri                  Galeri foto & video
/galeri/{kategori}       Galeri per kategori
/prestasi                Daftar prestasi
/anggota                 Direktori anggota (data publik)
/anggota/{slug}          Profil publik anggota
/materi                  Daftar materi terbuka (opsional)
/verifikasi/{kode}       Verifikasi sertifikat
/kontak                  Kontak & peta
/login                   Masuk panel
```

### 1.2 Panel Anggota (`/panel`)
```
/panel                   Dasbor (kegiatan mendatang, absensi, notifikasi)
/panel/profil            Profil saya (data internal milik sendiri)
/panel/kegiatan          Kegiatan saya + status absensi
/panel/kegiatan/{id}     Detail + QR check-in
/panel/materi            Materi pelatihan
/panel/quiz              Daftar quiz
/panel/quiz/{id}         Kerjakan quiz
/panel/nilai             Nilai saya
/panel/sertifikat        Sertifikat saya (download)
```

### 1.3 Panel Pengurus (`/admin`)
```
/panel/admin                    Dasbor admin (statistik ringkas)
/panel/admin/anggota            Kelola anggota (list, filter, import/export)
/panel/admin/anggota/create     Tambah anggota
/panel/admin/anggota/{id}       Detail (data publik + internal)
/panel/admin/kegiatan           Kelola kegiatan
/panel/admin/kegiatan/create    Buat kegiatan
/panel/admin/kegiatan/{id}      Kelola peserta & absensi
/panel/admin/absensi            Rekap absensi
/panel/admin/berita             Kelola berita/pengumuman
/panel/admin/galeri             Kelola galeri
/panel/admin/prestasi           Kelola prestasi
/panel/admin/program            Program kerja
/panel/admin/materi             Kelola materi
/panel/admin/quiz               Kelola quiz
/panel/admin/sertifikat         Kelola & generate sertifikat
/panel/admin/laporan            Laporan & statistik
```

### 1.4 Khusus Admin (`role=admin`)
```
/panel/admin/users              Kelola user & role
/panel/admin/master             Angkatan, kelas, jabatan
/panel/admin/settings           Pengaturan organisasi & sistem
/panel/admin/audit-log          Log audit
```

## 2. Struktur Menu

**Publik (header):** Beranda · Profil · Kegiatan · Berita · Galeri · Prestasi · Anggota · Kontak · [Masuk]

**Sidebar panel** (gelap, ringkas — grup Kelola memakai lipatan `<details>` native yang otomatis terbuka saat halamannya aktif, `x-layouts.panel` + `x-panel-nav-link`):
```
Saya
├── Dasbor
├── Materi · Quiz · Nilai · Sertifikat Saya
└── Notifikasi
Kelola (pengurus)
├── Area Pengurus · Anggota · Kegiatan (langsung)
├── ▸ Akademi (Kelola Materi · Kelola Quiz · Kelola Sertifikat)
├── ▸ Konten (Berita · Galeri · Prestasi · Program · Inbox)
└── ▸ Data & Laporan (Rekap Absensi · Laporan · Statistik Anggota · Statistik Kegiatan)
Admin
├── Area Admin
└── Pengguna · Master · Pengaturan · Audit Log
Keluar
```
Halaman yang terlipat tetap terjangkau via kartu **Akses Cepat** di dasbor pengurus.
Menu yang tidak berhak diakses **disembunyikan**, dan route tetap diproteksi Policy.

## 3. Alur Pengguna Utama

### 3.1 Pengurus membuat kegiatan + absensi QR
```
Pengurus login → Kegiatan → [+ Buat] → isi form → Simpan (draft/published)
  → Buka kegiatan → [+ Tambah Peserta] → pilih anggota → simpan
  → Sistem generate qr_token per peserta
  → Peserta scan QR → /kegiatan/{id}/check-in?token=xxx
  → AttendancePolicy::checkInOwn → status "hadir", method "qr"
  → Pengurus melihat rekap absensi → export
```

### 3.2 Admin import anggota
```
Admin → Anggota → Import → unduh template.xlsx → isi → upload
  → Validasi baris (duplikat NISN, format tgl, kelas ada)
  → Tampilkan ringkasan: X valid, Y error (dengan alasan)
  → Konfirmasi → insert/update → audit log "import"
```

### 3.3 Anggota mengerjakan quiz → sertifikat
```
Anggota → Materi (opsional) → Quiz → Mulai (timer jalan)
  → Submit → skor dihitung otomatis
  → Jika ≥ pass_score → job GenerateSertifikat → PDF + kode unik
  → Anggota download; publik bisa cek di /verifikasi/{kode}
```

### 3.4 Publik menelusuri anggota
```
Beranda → Anggota → filter kelas/angkatan → klik nama
  → /anggota/{slug} → hanya nama, kelas, jabatan, foto, angkatan, status
```

## 4. Komponen UI Reusable
- `x-layouts.public`, `x-layouts.panel`
- `x-card`, `x-stat-card` (prop opsional `icon`), `x-alert` (dengan ikon), `x-modal`, `x-badge` (status)
- `x-icon` (ikon inline gaya Heroicons outline, tanpa dependensi baru — `name="calendar"` dll.)
- `x-btn` (varian: `primary`, `secondary`, `light`, `outline-light`, `ghost`; jadi `<a>` bila ada `href`)
- `x-page-header` (`eyebrow` + `title` + `description` + slot `actions`) — kepala halaman publik
- `x-data-table` (judul kolom, sort, pagination, empty state)
- `x-upload` (drag-drop + preview + progress)
- `x-empty-state` (prop opsional `icon` + slot CTA)
- `x-qr-display` (QR per peserta)
- Nav publik: sticky + drawer seluler (JS vanilla inline, tanpa string `serviceWorker` agar `PwaTest` tetap hijau)
- Livewire: `search`, `filter`, `paginate` traits

## 5. State & Feedback
- Setiap aksi punya feedback: `session('success')` / `session('error')` → toast.
- Konfirmasi dialog untuk aksi destruktif (hapus, nonaktifkan).
- Loading state pada tombol submit Livewire (`wire:loading`).
- Empty state terarah (bukan halaman kosong).

## 6. Aksesibilitas & Responsif
- Kontras teks minimal 4.5:1.
- Semua `<input>` punya `<label>`.
- Navigasi keyboard: fokus terlihat, modal bisa ditutup ESC.
- Breakpoint Tailwind: `sm 640 / md 768 / lg 1024 / xl 1280`.
- Tabel di mobile: transform jadi kartu atau scroll horizontal.
- Alt text wajib pada gambar berita/galeri.

## 7. SEO (halaman publik)
- `<title>`, `<meta description>`, Open Graph, canonical per halaman.
- Sitemap otomatis (`/sitemap.xml`) Phase 4.
- Structured data `Organization` untuk halaman profil.

## 8. Navigasi Error
- 404: halaman ramah + tombol ke beranda.
- 403: "Anda tidak memiliki akses" + tombol kembali.
- Maintenance mode aktifkan saat deploy.
