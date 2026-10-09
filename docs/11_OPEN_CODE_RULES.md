# 11 — Aturan Kerja AI Coding Agent (OpenCode)

Dokumen ini adalah panduan khusus untuk agent. Aturan utama juga ada di `/AGENTS.md` (root) — **AGENTS.md adalah entry point**, dokumen ini versi lengkap.

## 1. Sumber Kebenaran
1. `AGENTS.md` (root) — aturan wajib singkat.
2. `docs/00–12` — blueprint lengkap. Jika percakapan bertentangan dengan `docs/`, **ikuti `docs/`** dan tanyakan user apakah dokumen perlu diperbarui.
3. Kode yang sudah ada — ikuti pola yang sudah ada sebelum membuat pola baru.

## 2. Urutan Kerja yang Benar
```
1. Baca AGENTS.md
2. Baca dokumen relevan (mis. 04_DATABASE.md saat bikin model)
3. Cek kode sekitar (model/policy/view tetangga) untuk meniru gaya
4. Buat/ubah kode
5. Buat/ubah migration bila skema berubah
6. Tulis/perbarui test
7. Jalankan: php artisan test
8. Baru selesai / laporkan hasil
```

## 3. Larangan
- ❌ Memperkenalkan framework/library baru tanpa approval user.
- ❌ Mengubah skema database tanpa migration.
- ❌ Menghapus data di migration (drop kolom berisi data, truncate).
- ❌ Menghapus/menurunkan test yang ada agar "lulus".
- ❌ Menampilkan data internal (NISN, alamat, no HP, tgl lahir, data ortu) di route/view publik.
- ❌ Melewati Policy (`authorize`) demi kecepatan.
- ❌ Menaruh secret/key di kode atau commit.
- ❌ Menjalankan `migrate` ke produksi tanpa backup.
- ❌ Mengubah `main` secara langsung (bila pakai branch).
- ❌ Menebak konvensi — kalau ragu, tanya atau cek tetangga code.

## 4. Kewajiban
- ✅ Ikuti arsitektur layering: Route → Policy → FormRequest → Action/Service → Model → View.
- ✅ Validasi semua input via FormRequest.
- ✅ Validasi semua file upload (MIME + ukuran).
- ✅ Pisahkan payload publik vs internal (`toPublicArray()`).
- ✅ Tulis test untuk logika bisnis kritis.
- ✅ Jalankan test sebelum mengklaim selesai.
- ✅ Update dokumen `docs/` bila perilaku/aturan berubah.
- ✅ Gunakan naming & style yang sudah ada.

## 5. Gaya Kode
- PHP 8.3+, strict types where applicable, type hint penuh.
- Bahasa identitas (nama tabel/kolom/komponen): konsisten — pilih Indonesia untuk domain (anggota, kegiatan) dan Inggris untuk istilah teknis. Saat mulai coding, tetapkan satu convention di `docs/` dan pertahankan.
- Komentar hanya bila perlu menjelaskan "kenapa", bukan "apa".
- Method kecil, satu tanggung jawab.
- Livewire: property publik = state tampilan; jangan logika bisnis di component.

## 6. Cara Menjawab Saat Diminta Fitur Besar
Jangan langsung menulis semua. Sebaliknya:
1. Identifikasi modul & dokumen terkait.
2. Buat rencana kecil (model → migration → policy → route → livewire → view → test).
3. Kerjakan bertahap, konfirmasi bila menyentuh keputusan penting (skema, role, security).
4. Selesai satu unit → test → lanjut.

## 7. Bila Ada Konflik
| Situasi | Tindakan |
|---|---|
| User minta melanggar aturan docs | Tegur singkat, tanyakan apakah docs mau diupdate |
| Bug di kode existing | Perbaiki terpisah dari fitur baru |
| Tidak yakin kebutuhan | Tanya, jangan menebak |
| Scope terlalu besar | Pecah jadi task kecil |

## 8. Perintah yang Sering Dipakai
```bash
docker compose up -d
docker compose exec app php artisan test
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:rollback
docker compose exec app php artisan make:model Member -mf
docker compose exec app php artisan make:livewire Members.MemberIndex
docker compose exec app php artisan make:policy MemberPolicy --model=Member
docker compose exec app php artisan make:test PrivacyTest
docker compose exec app composer dump-autoload
npm run build
```
