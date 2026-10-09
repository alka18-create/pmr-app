<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'pengurus@pmr.test'],
            [
                'name' => 'Pengurus Demo',
                'password' => Hash::make('password'),
                'role' => UserRole::Pengurus,
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ],
        );

        User::firstOrCreate(
            ['email' => 'anggota@pmr.test'],
            [
                'name' => 'Anggota Demo',
                'password' => Hash::make('password'),
                'role' => UserRole::Anggota,
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ],
        );
    }
}
