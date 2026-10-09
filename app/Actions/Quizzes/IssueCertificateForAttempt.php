<?php

namespace App\Actions\Quizzes;

use App\Enums\CertificateSourceType;
use App\Jobs\GenerateCertificate;
use App\Models\Certificate;
use App\Models\QuizAttempt;
use App\Notifications\CertificateReadyNotification;
use App\Services\AuditLogger;
use App\Services\Notifier;

class IssueCertificateForAttempt
{
    public function execute(QuizAttempt $attempt): ?Certificate
    {
        $existing = Certificate::query()
            ->where('member_id', $attempt->member_id)
            ->where('source_type', CertificateSourceType::Quiz)
            ->where('source_id', $attempt->quiz_id)
            ->where('is_revoked', false)
            ->first();

        if ($existing) {
            return $existing;
        }

        $certificate = Certificate::create([
            'code' => Certificate::generateUniqueCode(),
            'member_id' => $attempt->member_id,
            'source_type' => CertificateSourceType::Quiz,
            'source_id' => $attempt->quiz_id,
            'title' => 'Sertifikat: '.$attempt->quiz->title,
            'issued_at' => now()->toDateString(),
            'is_revoked' => false,
            'created_by' => null,
        ]);

        app(AuditLogger::class)->log(
            'created',
            $certificate,
            [],
            $certificate->getAttributes(),
        );

        app(Notifier::class)->toMember(
            $attempt->member,
            new CertificateReadyNotification($certificate->title, $certificate->code),
        );

        GenerateCertificate::dispatch($certificate->id)
            ->onQueue('certificates')
            ->afterCommit();

        return $certificate;
    }
}
