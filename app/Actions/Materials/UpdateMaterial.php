<?php

namespace App\Actions\Materials;

use App\Models\Material;
use App\Services\AuditLogger;

class UpdateMaterial
{
    public function execute(Material $material, array $data): Material
    {
        $old = $material->getAttributes();
        $material->update($data);

        app(AuditLogger::class)->log(
            'updated',
            $material,
            $old,
            $material->getChanges(),
        );

        return $material;
    }
}
