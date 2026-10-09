# 01 — PRD (Product Requirements Document)

## 1. Latar Belakang
Organisasi PMR (Palang Merah Remaja) di tingkat SMA masih banyak mengelola data secara manual: absensi di kertas, data anggota di spreadsheet, dokumentasi kegiatan tersebar di grup chat. Hal ini menyulitkan pelaporan, kehilangan data, dan tidak ada arsip terpusat.

## 2. Tujuan Produk
1. Menyediakan website resmi organisasi PMR yang profesional (profil, kegiatan, prestasi, berita).
2. Menyediakan sistem manajemen internal: anggota, kegiatan, absensi, konten, materi, sertifikat.
3. Menjaga kerahasiaan data pribadi siswa (hanya data publik yang ditampilkan).
4. Memudahkan pelaporan (rekap absensi, statistik, export PDF/Excel).

## 3. Sasaran (Goals & Metrics)
| Sasaran | Metrik |
|---|---|
| Website publik live | ≤ 3 bulan sejak mulai |
| Absensi kertas tergantikan | 100% kegiatan dicatat digital |
| Data anggota terpusat | ≥ 90% anggota terdaftar |
| Keamanan data pribadi | 0 kebocoran data internal ke publik |
| Performa halaman publik | LCP < 2.5s di 4G |

## 4. Persona
| Persona | Deskripsi | Kebutuhan Utama |
|---|---|---|
| **Tamu/Publik** | Siswa, orang tua, guru, masyarakat | Lihat profil, kegiatan, berita, galeri, prestasi |
| **Anggota (Siswa)** | Anggota PMR aktif | Absensi sendiri, materi, quiz, lihat sertifikat & profil |
| **Pengurus (Siswa)** | Ketua/bendahara/koordinator bidang | Kelola anggota, kegiatan, absensi peserta, konten |
| **Admin (Pembina/Guru)** | Pembina PMR / admin sekolah | Kelola pengguna, role, settings, audit log, laporan |

## 5. Ruang Lingkup (Scope)

### 5.1 In Scope (V1 — Phase 1–4)
- Autentikasi & otorisasi (3 role)
- Profil organisasi & pengaturan
- Data anggota + angkatan + kelas + jabatan + status
- Import/Export Excel data anggota
- Kegiatan/agenda + peserta + absensi + dokumentasi
- Berita/artikel/pengumuman publik
- Galeri foto & video
- Prestasi
- Profil publik anggota (data publik saja)

### 5.2 In Scope (V2 — Phase 5–6)
- Materi, quiz, nilai, sertifikat + verifikasi QR
- Laporan: rekap absensi, statistik, export PDF/Excel

### 5.3 Out of Scope (V1)
- Multi-sekolah / multi-organisasi
- Aplikasi mobile native
- Pembayaran iuran
- Integrasi API eksternal (PMI pusat, dsb)
- Microservices / frontend terpisah

## 6. Posisi Produk
- **Bukan** pengganti aplikasi PMI pusat — ini sistem manajemen tingkat sekolah.
- **Utamakan** kemudahan operasional harian pengurus vs fitur canggih.

## 7. Penerima Manfaat
- Organisasi PMR sekolah (pengurus & anggota)
- Pembina/guru pembimbing
- Sekolah (dokumentasi ekstrakurikuler)
- Publik sekolah (transparansi kegiatan)

## 8. Risiko & Mitigasi
| Risiko | Mitigasi |
|---|---|
| Scope terlalu besar untuk 1 tim kecil | Ikuti roadmap 7 fase; V1 = Phase 1–4 |
| Kebocoran data siswa | Pisahkan data publik/internal di level model (caster/API resource) + Policy |
| Server VPS mati/hilang | Backup harian ke lokasi off-VPS |
| Regresi saat development | Test critical path + staging deploy |
| Perubahan kebutuhan berulang | Semua perubahan lewat update dokumen `docs/` dulu, lalu kode |

## 9. Success Criteria V1
1. Website publik menampilkan profil, berita, galeri, prestasi, daftar anggota (data publik).
2. Pengurus bisa membuat kegiatan, menambah peserta, mencatat absensi.
3. Admin bisa mengelola user & role.
4. Tidak ada field data internal yang muncul di halaman publik (dicek test otomatis).
5. Deploy stabil via Docker Compose di VPS + Cloudflare.
