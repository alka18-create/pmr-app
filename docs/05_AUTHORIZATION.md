# 05 — Authorization (Role & Permission)

## 1. Model Role
3 role, disimpan di kolom `users.role` (V1 sederhana) dan siap di-upgrade ke Spatie Permission bila perlu granular.

| Role | Siapa | Login |
|---|---|---|
| `admin` | Pembina PMR / guru / admin sekolah | ya |
| `pengurus` | Ketua, sekretaris, bendahara, koordinator bidang | ya |
| `anggota` | Anggota PMR biasa | ya |
| *(tamu)* | Publik tanpa login | tidak |

## 2. Matriks Permission

| Resource / Aksi | publik | anggota | pengurus | admin |
|---|:-:|:-:|:-:|:-:|
| Lihat halaman publik (beranda, profil, berita published, galeri, prestasi, agenda) | ✅ | ✅ | ✅ | ✅ |
| Lihat profil publik anggota (nama, kelas, jabatan, foto, angkatan, status) | ✅ | ✅ | ✅ | ✅ |
| Verifikasi sertifikat publik | ✅ | ✅ | ✅ | ✅ |
| Login ke panel | ❌ | ✅ | ✅ | ✅ |
| Lihat dashboard | ❌ | dasar | ✅ | ✅ |
| **Data internal anggota** (NISN, alamat, no HP, tgl lahir, ortu) | ❌ | ❌* | ✅ | ✅ |
| Tambah/ubah/nonaktifkan anggota | ❌ | ❌ | ✅ | ✅ |
| Import/Export anggota | ❌ | ❌ | ✅ | ✅ |
| Kelola angkatan, kelas, jabatan | ❌ | ❌ | ❌ | ✅ |
| Buat/ubah kegiatan | ❌ | ❌ | ✅ | ✅ |
| Hapus kegiatan | ❌ | ❌ | ❌ | ✅ |
| Kelola peserta & absensi kegiatan | ❌ | ❌** | ✅ | ✅ |
| Lihat absensi sendiri | ❌ | ✅ | ✅ | ✅ |
| Buat/ubah artikel, galeri, prestasi, program | ❌ | ❌ | ✅ | ✅ |
| Publish/hapus konten | ❌ | ❌ | ✅ | ✅ |
| Kelola materi, quiz, sertifikat | ❌ | ❌ | ✅ | ✅ |
| Kerjakan quiz | ❌ | ✅ | ✅ | ✅ |
| Lihat nilai & sertifikat sendiri | ❌ | ✅ | ✅ | ✅ |
| Lihat laporan & statistik | ❌ | ❌ | ✅ | ✅ |
| Export PDF/Excel laporan | ❌ | ❌ | ✅ | ✅ |
| Kelola user & role | ❌ | ❌ | ❌ | ✅ |
| Pengaturan sistem | ❌ | ❌ | ❌ | ✅ |
| Lihat audit log | ❌ | ❌ | ❌ | ✅ |
| Backup/restore | ❌ | ❌ | ❌ | ✅ (via opsional) |

\* Anggota hanya melihat **profil publik** milik sendiri dan data internal miliknya sendiri di halaman profil pribadi.
\** Anggota bisa konfirmasi kehadiran diri sendiri (self check-in via QR bila diaktifkan), bukan mengelola peserta lain.

## 3. Peta Permission (dipakai di Policy)

```
member.view-public        → publik
member.view-internal      → pengurus, admin
member.create / update / delete → pengurus, admin
member.import / export    → pengurus, admin
master.manage             → admin (angkatan, kelas, jabatan)

event.view                → publik (is_public), anggota, pengurus, admin
event.create / update     → pengurus, admin
event.delete              → admin
attendance.manage         → pengurus, admin
attendance.view-own       → anggota, pengurus, admin

content.view              → publik (is_public)
content.create / update / delete → pengurus, admin

academy.view              → anggota, pengurus, admin
academy.manage            → pengurus, admin
certificate.verify-public → publik

report.view / export      → pengurus, admin

user.manage               → admin
settings.manage           → admin
audit.view                → admin
```

## 4. Policy (satu model satu policy)

| Policy | Metode kunci |
|---|---|
| `MemberPolicy` | `view`, `viewInternal`, `create`, `update`, `delete`, `import`, `export` |
| `EventPolicy` | `view`, `create`, `update`, `delete` (delete: pembuat atau admin) |
| `AttendancePolicy` | `manage`, `viewOwn`, `checkInOwn` |
| `ArticlePolicy` | `view`, `create`, `update`, `delete` |
| `GalleryItemPolicy` | sama seperti Article |
| `AchievementPolicy` | sama seperti Article |
| `MaterialPolicy` | `view`, `manage` |
| `QuizPolicy` | `view`, `manage`, `attempt` |
| `CertificatePolicy` | `viewOwn`, `verify`, `manage` |
| `UserPolicy` | `viewAny`, `create`, `update`, `delete` |
| `SettingPolicy` | `manage` |
| `AuditLogPolicy` | `viewAny` (admin saja) |

Aturan:
1. **Wajib** memanggil `$this->authorize(...)` / `Gate` di setiap controller/Livewire yang menyentuh data sensitif.
2. Livewire: gunakan `#[Authorize]` attribute atau panggil policy di `mount()`.
3. Route publik didefinisikan terpisah (`routes/web.php` grup `public`), route panel di grup `auth` + middleware `role:admin|pengurus|anggota`.
4. Menu disembunyikan berdasarkan permission (UI), **tapi** otorisasi tetap diverifikasi di server (jangan percaya UI).

## 5. Prinsip Kerahasiaan Data
- Kolom internal diberi accessor terpisah:
  - `Member::publicFields()` → untuk view publik.
  - `Member::internalFields()` → hanya dipanggil di view internal yang sudah lewat `viewInternal`.
- Livewire component yang menampilkan data internal **wajib** punya properti `#[Locked]` dan method authorize.
- Test otomatis (`tests/Feature/PrivacyTest.php`) memastikan response halaman publik **tidak** mengandung nilai NISN/alamat/no. HP.

## 6. Contoh Penerapan (Livewire)

```php
// app/Livewire/Members/MemberShow.php
public function mount(int $id): void
{
    $member = Member::findOrFail($id);

    if ($member->isPublicProfile()) {
        // data publik saja
        $this->member = $member->toPublicArray();
        return;
    }

    $this->authorize('viewInternal', $member); // lempar 403 jika bukan pengurus/admin
    $this->member = $member->toArray();        // data lengkap
}
```
