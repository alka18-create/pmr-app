<?php

namespace App\Jobs;

use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GenerateCertificate implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $certificateId,
    ) {
        $this->onQueue('certificates');
    }

    public function handle(): void
    {
        $certificate = Certificate::with(['member.user', 'creator'])->find($this->certificateId);

        if (! $certificate || $certificate->is_revoked) {
            return;
        }

        $verifyUrl = route('verify.certificate', ['code' => $certificate->code]);

        $qrResult = (new Builder)
            ->build(
                data: $verifyUrl,
                size: 180,
                margin: 10,
            );

        $pdf = Pdf::loadView('certificates.pdf', [
            'certificate' => $certificate,
            'member' => $certificate->member,
            'qrDataUri' => $qrResult->getDataUri(),
            'verifyUrl' => $verifyUrl,
            'issuedAt' => $certificate->issued_at->translatedFormat('d F Y'),
        ])->setPaper('a4', 'landscape');

        $path = 'certificates/'.$certificate->code.'.pdf';
        Storage::disk('local')->put($path, $pdf->output());

        $certificate->update(['file_path' => $path]);
    }
}
