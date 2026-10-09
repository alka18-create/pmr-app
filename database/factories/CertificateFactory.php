<?php

namespace Database\Factories;

use App\Enums\CertificateSourceType;
use App\Models\Certificate;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Certificate>
 */
class CertificateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => Certificate::generateUniqueCode(),
            'member_id' => Member::factory(),
            'source_type' => CertificateSourceType::Quiz,
            'source_id' => null,
            'title' => 'Sertifikat Kelulusan Quiz',
            'issued_at' => now()->toDateString(),
            'file_path' => null,
            'is_revoked' => false,
            'created_by' => User::factory(),
        ];
    }

    public function revoked(): static
    {
        return $this->state(fn () => ['is_revoked' => true]);
    }
}
