<?php

namespace App\Actions\Gallery;

use App\Models\GalleryItem;
use App\Services\AuditLogger;

class DeleteGalleryItem
{
    public function execute(GalleryItem $item): void
    {
        $old = $item->getAttributes();
        $item->delete();

        app(AuditLogger::class)->log('deleted', $item, $old, []);
    }
}
