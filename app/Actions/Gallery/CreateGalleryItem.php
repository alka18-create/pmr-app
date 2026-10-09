<?php

namespace App\Actions\Gallery;

use App\Models\GalleryItem;
use App\Services\AuditLogger;

class CreateGalleryItem
{
    public function execute(array $data): GalleryItem
    {
        $item = GalleryItem::create($data);

        app(AuditLogger::class)->log(
            'created',
            $item,
            [],
            $item->getAttributes(),
        );

        return $item;
    }
}
