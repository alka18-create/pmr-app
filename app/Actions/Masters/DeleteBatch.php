<?php

namespace App\Actions\Masters;

use App\Models\Batch;
use App\Services\AuditLogger;

class DeleteBatch
{
    public function execute(Batch $batch): void
    {
        $old = $batch->getAttributes();
        $batch->delete();

        app(AuditLogger::class)->log('deleted', $batch, $old, []);
    }
}
