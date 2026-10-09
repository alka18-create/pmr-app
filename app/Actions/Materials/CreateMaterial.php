<?php

namespace App\Actions\Materials;

use App\Models\Material;
use App\Services\AuditLogger;

class CreateMaterial
{
    public function execute(array $data): Material
    {
        $material = Material::create($data);

        app(AuditLogger::class)->log(
            'created',
            $material,
            [],
            $material->getAttributes(),
        );

        return $material;
    }
}
