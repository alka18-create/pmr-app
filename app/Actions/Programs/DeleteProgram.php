<?php

namespace App\Actions\Programs;

use App\Models\Program;
use App\Services\AuditLogger;

class DeleteProgram
{
    public function execute(Program $program): void
    {
        $old = $program->getAttributes();
        $program->delete();

        app(AuditLogger::class)->log('deleted', $program, $old, []);
    }
}
