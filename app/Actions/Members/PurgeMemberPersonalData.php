<?php

namespace App\Actions\Members;

use App\Enums\MemberStatus;
use App\Enums\UserStatus;
use App\Models\Member;
use App\Services\AuditLogger;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Prosedur penghapusan data pribadi siswa (docs/08 §3.3, docs/13 §7.7).
 * Anonimkan kolom internal + soft delete + nonaktifkan akun login terkait.
 * Data agregat (absensi, nilai) dipertahankan untuk laporan.
 */
class PurgeMemberPersonalData
{
    public function execute(Member $member): Member
    {
        $old = Arr::except($member->getAttributes(), ['notes']);

        if ($member->photo_path) {
            Storage::disk('public')->delete($member->photo_path);
        }

        $user = $member->user;

        $member->forceFill([
            'user_id' => null,
            'nis' => null,
            'nisn' => null,
            'full_name' => 'Anggota Dihapus',
            'slug' => 'anggota-dihapus-'.$member->id,
            'birth_date' => null,
            'birth_place' => null,
            'address' => null,
            'phone' => null,
            'email_pribadi' => null,
            'parent_name' => null,
            'parent_phone' => null,
            'blood_type' => null,
            'photo_path' => null,
            'status' => MemberStatus::Nonaktif,
            'notes' => null,
        ]);
        // save() dulu — delete() Eloquent tidak menyimpan atribut dirty lain
        $member->save();
        $member->delete();

        if ($user) {
            $user->forceFill([
                'name' => 'Pengguna Dihapus',
                'email' => 'dihapus-'.$user->id.'@users.invalid',
                'password' => Str::random(32),
                'remember_token' => null,
                'status' => UserStatus::Inactive,
            ])->save();
        }

        app(AuditLogger::class)->log('deleted', $member, $old, [
            'anonymized' => true,
            'full_name' => 'Anggota Dihapus',
        ]);

        return $member;
    }
}
