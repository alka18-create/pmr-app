<?php

namespace App\Actions\Certificates;

use App\Enums\CertificateSourceType;
use App\Jobs\GenerateCertificate;
use App\Models\Certificate;
use App\Models\Member;
use App\Notifications\CertificateReadyNotification;
use App\Services\AuditLogger;
use App\Services\Notifier;
use Illuminate\Validation\ValidationException;

class IssueCertificate
{
    public function execute(
        int $memberId,
        CertificateSourceType $sourceType,
        ?int $sourceId,
        string $title,
        ?int $createdBy = null,
    ): Certificate {
        $existing = Certificate::query()
            ->where('member_id', $memberId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->where('is_revoked', false)
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'certificate' => 'Sertifikat untuk sumber ini sudah terbit.',
            ]);
        }

        $certificate = Certificate::create([
            'code' => Certificate::generateUniqueCode(),
            'member_id' => $memberId,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'title' => $title,
            'issued_at' => now()->toDateString(),
            'is_revoked' => false,
            'created_by' => $createdBy,
        ]);

        app(AuditLogger::class)->log(
            'created',
            $certificate,
            [],
            $certificate->getAttributes(),
        );

        app(Notifier::class)->toMember(
            Member::with('user')->find($memberId),
            new CertificateReadyNotification($title, $certificate->code),
        );

        GenerateCertificate::dispatch($certificate->id)
            ->onQueue('certificates')
            ->afterCommit();

        return $certificate;
    }
}
