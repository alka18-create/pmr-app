<?php

namespace App\Actions\Masters;

use App\Models\SchoolClass;
use App\Services\AuditLogger;

class DeleteSchoolClass
{
    public function execute(SchoolClass $schoolClass): void
    {
        $old = $schoolClass->getAttributes();
        $schoolClass->delete();

        app(AuditLogger::class)->log('deleted', $schoolClass, $old, []);
    }
}
