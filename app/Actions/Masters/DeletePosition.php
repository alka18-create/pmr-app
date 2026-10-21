<?php

namespace App\Actions\Masters;

use App\Models\Position;
use App\Services\AuditLogger;

class DeletePosition
{
    public function execute(Position $position): void
    {
        $old = $position->getAttributes();
        $position->delete();

        app(AuditLogger::class)->log('deleted', $position, $old, []);
    }
}
