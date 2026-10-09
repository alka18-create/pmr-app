# 02 — Requirements

Kode kebutuhan: `FR-<modul>-<nomor>`, `NFR-<nomor>`.

## A. Kebutuhan Fungsional

### Autentikasi & Pengguna (`FR-AUTH`)
- `FR-AUTH-01` Pengguna dapat login dengan email + password.
- `FR-AUTH-02` Sistem mengeluarkan pengguna otomatis setelah idle 2 jam / sesi 8 jam.
- `FR-AUTH-03` Admin dapat membuat akun baru dan menetapkan role (`admin`/`pengurus`/`anggota`).
- `FR-AUTH-04` Pengguna dapat merubah password sendiri.
- `FR-AUTH-05` Admin dapat menonaktifkan/mengaktifkan akun.
- `FR-AUTH-06` Login rate-limited (5 percobaan/menit per email+IP).

### Otorisasi (`FR-AUTHZ`)
- `FR-AUTHZ-01` Setiap akses sensitif melewati Laravel Policy.
- `FR-AUTHZ-02` Data internal anggota hanya bisa diakses role `admin` & `pengurus`.
- `FR-AUTHZ-03` Halaman publik tidak memerlukan login.

### Profil Organisasi (`FR-ORG`)
- `FR-ORG-01` Admin dapat mengelola profil organisasi (nama, sejarah, visi, misi, struktur pengurus, 7 prinsip, kontak, media sosial, logo).
- `FR-ORG-02` Admin dapat mengelola pengaturan sistem (nama sekolah, tahun ajaran, fitur on/off).

### Anggota (`FR-MEM`)
- `FR-MEM-01` Admin/Pengurus dapat menambah, mengubah, menonaktifkan data anggota.
- `FR-MEM-02` Data anggota memuat: identitas publik (nama, kelas, jabatan, foto, angkatan, status) dan data internal (NIS, NISN, tgl lahir, jenis kelamin, alamat, no. HP, email, data orang tua).
- `FR-MEM-03` Data internal hanya tampil di halaman internal.
- `FR-MEM-04` Profil publik `/anggota/{id}` hanya menampilkan: nama, kelas, jabatan, foto, angkatan, status keanggotaan.
- `FR-MEM-05` Dapat filter & pencarian: nama, kelas, angkatan, jabatan, status.
- `FR-MEM-06` Import data anggota dari file Excel (.xlsx) dengan validasi baris per baris.
- `FR-MEM-07` Export data anggota ke Excel sesuai hak akses.
- `FR-MEM-08` Angkatan (batch) dikelola Admin.
- `FR-MEM-09` Jabatan (ketua, sekretaris, bendahara, koordinator bidang, anggota) dikelola Admin.
- `FR-MEM-10` Riwayat jabatan anggota tercatat (mulai–selesai).

### Kegiatan & Absensi (`FR-EVT`)
- `FR-EVT-01` Pengurus/Admin dapat membuat kegiatan (judul, jenis, deskripsi, lokasi, waktu mulai–selesai, poster, status).
- `FR-EVT-02` Pengurus/Admin dapat menambah/menghapus peserta kegiatan.
- `FR-EVT-03` Sistem generate QR token unik per peserta untuk absensi.
- `FR-EVT-04` Absensi dapat dicatat: hadir, izin, sakit, alpa — manual oleh pengurus atau scan QR.
- `FR-EVT-05` Absensi hanya bisa diubah oleh pembuat kegiatan/Admin.
- `FR-EVT-06` Dokumentasi (foto/video) kegiatan dapat diunggah & ditampilkan di galeri bila ditandai publik.
- `FR-EVT-07` Agenda/jadwal kegiatan mendatang tampil di halaman publik.
- `FR-EVT-08` Anggota dapat melihat kegiatan miliknya dan status absensinya.

### Konten (`FR-CNT`)
- `FR-CNT-01` Pengurus/Admin dapat membuat artikel berita & pengumuman (draft/published).
- `FR-CNT-02` Hanya konten berstatus published yang tampil di publik.
- `FR-CNT-03` Galeri mendukung foto & video (YouTube embed/URL).
- `FR-CNT-04` Admin/Pengurus dapat mengelola kategori galeri.
- `FR-CNT-05` Prestasi dapat dicatat: judul, tingkat (sekolah/kota/nasional), tahun, deskripsi, lampiran, tampil/sembunyi di publik.
- `FR-CNT-06` Setiap konten publik punya SEO dasar: slug, meta title, meta description, share image.

### Akademi (`FR-ACA`) — Phase 5
- `FR-ACA-01` Admin/Pengurus dapat mengunggah materi (MD/PDF) per kategori.
- `FR-ACA-02` Admin/Pengurus dapat membuat quiz (pilihan ganda, nilai per soal, batas waktu).
- `FR-ACA-03` Anggota mengerjakan quiz sekali sesuai aturan (attempt dapat dibatasi).
- `FR-ACA-04` Nilai tersimpan otomatis & terlihat anggota bersangkutan.
- `FR-ACA-05` Sertifikat tergenerate (PDF) setelah lulus quiz/kegiatan.
- `FR-ACA-06` Sertifikat punya kode unik & halaman verifikasi publik `/verifikasi/{kode}`.

### Laporan (`FR-RPT`) — Phase 6
- `FR-RPT-01` Rekap absensi per kegiatan & per anggota.
- `FR-RPT-02` Statistik: jumlah anggota per kelas/angkatan, jumlah kegiatan per bulan.
- `FR-RPT-03` Export laporan ke PDF & Excel.

### Audit (`FR-AUD`)
- `FR-AUD-01` Perubahan data kritis (anggota, user, role, pengaturan) dicatat ke audit log: pelaku, aksi, model, ID, nilai lama–baru, IP, waktu.
- `FR-AUD-02` Hanya Admin yang dapat melihat audit log.

## B. Kebutuhan Non-Fungsional

| Kode | Kategori | Keterangan |
|---|---|---|
| `NFR-01` | Performa | Halaman publik LCP < 2.5s; API/internal < 1s (p95) |
| `NFR-02` | Skala | Mendukung ≤ 500 anggota, ≤ 50 user login bersamaan |
| `NFR-03` | Ketersediaan | Uptime target 99% (VPS 1 region) |
| `NFR-04` | Keamanan | OWASP Top 10 dicek; HTTPS wajib; input selalu divalidasi |
| `NFR-05` | Privasi | Field internal tidak pernah muncul di view publik (dijamin test) |
| `NFR-06` | Backup | Backup DB harian, retention 7 harian / 4 mingguan / 3 bulanan, off-VPS |
| `NFR-07` | Aksesibilitas | WCAG AA dasar: kontras, label form, navigasi keyboard |
| `NFR-08` | Responsif | Mobile-first, break: 640/768/1024/1280 |
| `NFR-09` | Browser | Chrome, Firefox, Safari, Edge (2 versi terakhir) |
| `NFR-10` | Bahasa | UI Bahasa Indonesia |
| `NFR-11` | Log | Error log ke file/Laravel Telescope (dev) & Sentry opsional (prod) |
| `NFR-12` | Migrasi | Semua perubahan skema wajib via migration, tanpa data deletion |

## C. Aturan Data Publik vs Internal (ringkas)

| Publik (boleh tampil) | Internal (RAHASIA) |
|---|---|
| Nama lengkap | NIS / NISN |
| Kelas | Tanggal lahir lengkap |
| Jabatan | Jenis kelamin |
| Foto profil | Alamat |
| Angkatan ("Anggota sejak 2026") | No. HP / email pribadi |
| Status keanggotaan | Data orang tua (nama, no. HP) |
| Prestasi, kegiatan, konten | Password, token, audit log |
