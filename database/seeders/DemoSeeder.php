<?php

namespace Database\Seeders;

use App\Enums\MemberStatus;
use App\Models\Batch;
use App\Models\Member;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Data demo khusus development: tautkan akun demo ke profil anggota
 * agar alur anggota (quiz, absensi, sertifikat) bisa dicoba langsung.
 * Hanya jalan di environment local.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        foreach (['anggota@pmr.test', 'pengurus@pmr.test'] as $email) {
            $user = User::query()->where('email', $email)->first();

            if ($user === null || $user->member !== null) {
                continue;
            }

            Member::factory()->create([
                'user_id' => $user->id,
                'full_name' => $user->name,
                'status' => MemberStatus::Aktif->value,
                'batch_id' => Batch::query()->first()?->id ?? Batch::factory(),
                'class_id' => SchoolClass::query()->first()?->id ?? SchoolClass::factory(),
            ]);
        }
    }
}
