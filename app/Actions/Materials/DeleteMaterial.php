<?php

namespace App\Actions\Materials;

use App\Models\Material;
use App\Services\AuditLogger;

class DeleteMaterial
{
    public function execute(Material $material): void
    {
        app(AuditLogger::class)->log(
            'deleted',
            $material,
            $material->getAttributes(),
            [],
        );

        $material->delete();
    }
}
