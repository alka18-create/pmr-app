# 10 — Testing

## 1. Framework
- **Pest PHP** (syntax ringan, berbasis PHPUnit) — atau PHPUnit murni bila tim lebih suka.
- Livewire testing bawaan Livewire.
- Test DB: gunakan database terpisah (`testing`), `RefreshDatabase`.
- CI: GitHub Actions (Phase 7) menjalankan seluruh suite.

## 2. Piramida Test

```
        E2E (sedikit, opsional Dusk)
      Integration / Feature (banyak)
   Unit (logika bisnis murni)
```

Prioritas: **Feature test > Unit test > E2E.**

## 3. Test Kritis Wajib (selalu hijau)

### 3.1 Privacy Test (`tests/Feature/PrivacyTest.php`)
- Response halaman publik (`/anggota`, `/anggota/{slug}`) **tidak** mengandung nilai `nisn`, `alamat`, `phone`, `birth_date`, `parent_phone` dari seed.
- Response route publik tidak mengembalikan kolom internal via resource.
-(anggota biasa) tidak bisa `GET` data internal anggota lain → 403.

### 3.2 Authorization Test (`tests/Feature/AuthorizationTest.php`)
Untuk tiap Policy, uji:
- `guest` → diarahkan login untuk route panel.
- `anggota` → 403 untuk route pengurus/admin.
- `pengurus` → boleh CRUD member/event/content, 403 untuk users/settings/audit.
- `admin` → akses penuh.

### 3.3 Autentikasi
- Login sukses/gagal, rate limit aktif.
- Logout menghapus sesi.
- Reset password: token valid & kedaluwarsa.

### 3.4 Bisnis Inti
| Fitur | Test |
|---|---|
| Import anggota | valid baris masuk, baris error dilaporkan, duplikat NISN ditolak |
| Absensi QR | token valid → hadir; token duplikat → ditolak; token salah → 404 |
| Absensi manual | hanya pengurus pembuat/admin yang bisa ubah |
| Quiz | skor dihitung benar; batas attempt ditekan; habis waktu → expired |
| Sertifikat | skor ≥ pass → sertifikat dibuat; kode unik; verifikasi publik valid/invalid |
| Konten | hanya `published & is_public` tampil di publik |
| Export | hanya role berhak; audit log terisi |

### 3.5 Validasi Form
Setiap FormRequest punya test input tidak valid → error spesifik.

## 4. Test yang Boleh Ditunda
- E2E penuh (Laravel Dusk) — Phase 6.
- Visual regression — tidak perlu.
- Load test — tidak perlu (skala kecil).

## 5. Konvensi Penamaan
```php
it('membatasi login 5 kali per menit', ...);
it('tidak menampilkan nisn di halaman publik anggota', ...);
it('menolak anggota biasa mengakses data internal', ...);
```
Gunakan `it()`/`test()` deskriptif Bahasa Indonesia.

## 6. Perintah
```bash
docker compose exec app php artisan test          # semua
docker compose exec app php artisan test --filter=PrivacyTest
docker compose exec app php artisan test tests/Feature
docker compose exec app php artisan test --coverage   # lokal saja
```

## 7. Definition of Done (per task)
1. Fitur berjalan manual.
2. Test baru/terkait ditulis & hijau.
3. Seluruh suite hijau (`php artisan test`).
4. Tidak ada perubahan skema tanpa migration.
5. Tidak ada logika sensitif di view tanpa Policy.
6. Docs di-update bila ada perubahan perilaku/aturan.
