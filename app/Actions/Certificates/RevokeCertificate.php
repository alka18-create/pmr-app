<?php

namespace App\Actions\Certificates;

use App\Models\Certificate;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Storage;

class RevokeCertificate
{
    public function execute(Certificate $certificate): Certificate
    {
        $old = $certificate->getAttributes();

        if ($certificate->file_path) {
            Storage::disk('local')->delete($certificate->file_path);
        }

        $certificate->update([
            'is_revoked' => true,
            'file_path' => null,
        ]);

        app(AuditLogger::class)->log(
            'updated',
            $certificate,
            $old,
            $certificate->getChanges(),
        );

        return $certificate;
    }
}
