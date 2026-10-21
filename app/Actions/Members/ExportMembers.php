<?php

namespace App\Actions\Members;

use App\Exports\MembersExport;
use App\Services\AuditLogger;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class ExportMembers
{
    public function download(?int $classId = null, ?int $batchId = null, ?string $status = null): Response
    {
        app(AuditLogger::class)->log('export', null, [], [
            'resource' => 'members',
            'class_id' => $classId,
            'batch_id' => $batchId,
            'status' => $status,
        ]);

        $name = 'anggota.xlsx';

        return Excel::download(
            new MembersExport($classId, $batchId, $status),
            $name,
        );
    }
}
