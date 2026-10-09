# 08 — Security

Konteks: aplikasi menyimpan **data pribadi siswa di bawah umur**. Tingkat keharusan: tinggi.

## 1. Prinsip
1. **Data publik ≠ data internal.** Pisahkan sejak level model, bukan sekadar menyembunyikan tombol.
2. **Jangan percaya input klien.** Selalu validasi & otorisasi ulang di server.
3. **Default deny.** Route/aksi tidak diberi akses kecuali diizinkan Policy.
4. **Prinsip least privilege** untuk DB user & VPS.
5. **Log semua aksi sensitif.**

## 2. Keamanan Aplikasi

### 2.1 Autentikasi
- Password: `bcrypt`/`argon2`, minimum 8 karakter.
- Rate limit login: 5 attempt/menit per email+IP, lock 15 menit.
- Session: `secure`, `http_only`, `same_site=lax`.
- Regenerasi session ID setelah login.
- Logout menghapus session + remember token.
- Reset password: token sekali pakai, kedaluwarsa 60 menit.

### 2.2 Otorisasi
- Setiap akses data memakai Policy (lihat `05_AUTHORIZATION.md`).
- Field internal tidak pernah muncul di view publik → dijamin `tests/Feature/PrivacyTest.php`.
- Livewire: properti sensitif diberi `#[Locked]`.
- Jangan mengandalkan `@role` di Blade saja; selalu cek di server.

### 2.3 Input & Upload
- Semua input via FormRequest dengan rules eksplisit.
- Escape output Blade `{{ }}` (default). `{!! !!}` hanya untuk konten yang sudah di-sanitize (HTMLPurifier) — misal body artikel.
- Upload file: validasi MIME/extension whitelist, ukuran maks (foto 2 MB, PDF 5 MB), rename random, simpan **di luar** `public/` bila berisi data sensitif (kecuali foto profil yang memang publik).
- Eksekusi file upload mustahil: `storage` tidak bisa di-serve script.

### 2.4 Web
- CSRF protection aktif untuk semua form POST.
- Content-Security-Policy + header keamanan lain via middleware global Laravel `App\Http\Middleware\SecurityHeaders` (env-aware: longgar di dev termasuk HMR Vite; di produksi hanya origin eksternal Google Fonts — `style-src fonts.googleapis.com`, `font-src fonts.gstatic.com` — untuk font Plus Jakarta Sans, docs/06) — diketatkan bertahap; file statis (tidak lewat PHP) dapat header dasar via nginx.
- Headers: `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy`.
- HTTPS dipaksa di Cloudflare (Full Strict) + redirect di Nginx.
- Nonaktifkan `APP_DEBUG` di produksi.
- Sembunyikan versi Laravel & header server yang tidak perlu.
- Cek: `tests/Feature/SecurityHeadersTest.php`.

### 2.5 SQL Injection / Mass Assignment
- Eloquent/bind query, jangan string concat query.
- `$fillable`/`$guarded` diketjil per model; **jangan** `protected $guarded = []`.
- Larangan `Model::create($request->all())`.

### 2.6 Rate Limiting
- Login, form kontak, check-in QR, verifikasi sertifikat → rate limited.
- Global throttle 60 request/menit/IP untuk route publik.

## 3. Keamanan Data Privasi

### 3.1 Klasifikasi
| Level | Data | Aturan |
|---|---|---|
| Publik | nama, kelas, jabatan, foto, angkatan, status, konten | tampil bebas |
| Internal | NIS/NISN, tgl lahir, alamat, no HP, email, darah, ortu | hanya pengurus/admin |
| Rahasia | password, token, QR token, audit log | tidak pernah ditampilkan/dikirim |

### 3.2 Aturan Teknis
1. `Member::toPublicArray()` satu-satunya sumber data untuk view publik.
2. Route publik tidak boleh query kolom internal (cek di test: assert response tidak mengandung nilai `nisn` seed).
3. Log tidak boleh mencetak data internal (filtered `password`, `token`, `nisn`).
4. Export Excel data internal: hanya role berhak, dicatat di audit log.
5. Screenshot/dokumen laporan dibagikan via link internal, bukan publik.

### 3.3 Retensi
- Data alumni: tetap tersimpan, status `alumni`, bisa disembunyikan dari direktori publik via setting.
- Permintaan penghapusan data pribadi siswa: **fitur admin** (Phase 7 ✅) — tombol "Hapus Data Pribadi" di detail anggota → `PurgeMemberPersonalData` (anonimkan kolom internal + nama, soft delete, nonaktifkan & anonimkan akun login, audit log `deleted`). Cek: `tests/Feature/PrivacyDeletionTest.php`.
- Log Laravel: driver `daily`, retensi 14 hari (`LOG_DAILY_DAYS=14`).

## 4. Keamanan Server & Docker
- VPS: firewall `uff` hanya buka 22 (SSH key-only, port custom), 80, 443.
- SSH: matikan password auth, matikan root login, pakai key.
- Docker: jangan map port Postgres/Redis ke host publik; hanya di network internal compose.
- DB user aplikasi hanya punya privilege `SELECT/INSERT/UPDATE/DELETE` pada skema aplikasi.
- Update base image & dependency rutin (monthly).
- `APP_KEY` aman di `.env`, tidak masuk git.
- `.env` & backup tidak boleh di-commit. `.gitignore` dicek.

## 5. Keamanan Dependencies
- `composer audit` / `npm audit` jalankan saat setup & sebelum release.
- Dependabot/Renovate alerts aktif di GitHub (opsional).

## 6. Audit Log
Setiap aksi berikut wajib masuk `audit_logs`:
- login, logout, login gagal (opsional)
- create/update/delete: user, member, event, article, setting, role
- import, export
- generate/merevoke sertifikat
- perubahan password

Kolom: user_id, action, model_type, model_id, old_values, new_values, ip, user_agent, created_at.

## 7. Incident Response (ringkas)
1. **Curiga kebocoran** → nonaktifkan akun terkait, rotate `APP_KEY`/password admin, cek audit log.
2. **Server compromised** → restore dari backup terakhir bersih, ganti semua kredensial.
3. Dokumentasikan kejadian di `docs/incidents/` (belakangan).

## 8. Checklist Sebelum Rilis
- [ ] `APP_DEBUG=false`
- [ ] Semua route publik dicek PrivacyTest lolos
- [ ] Rate limit login & form aktif
- [ ] Header keamanan terpasang
- [ ] Upload file tervalidasi & tidak executable
- [ ] `.env` tidak di git; `APP_KEY` ada
- [ ] Postgres/Redis tidak terekspos publik
- [ ] Backup otomatis berjalan & pernah diuji restore
- [ ] `composer audit` / `npm audit` bersih dari critical
