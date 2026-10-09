<?php

namespace App\Actions\Gallery;

use App\Models\GalleryItem;
use App\Services\AuditLogger;

class UpdateGalleryItem
{
    public function execute(GalleryItem $item, array $data): GalleryItem
    {
        $old = $item->getAttributes();
        $item->update($data);

        app(AuditLogger::class)->log(
            'updated',
            $item,
            $old,
            $item->getChanges(),
        );

        return $item;
    }
}
