<?php

namespace App\Actions\Gallery;

use App\Models\GalleryCategory;
use App\Services\AuditLogger;

class DeleteGalleryCategory
{
    /**
     * Hapus kategori galeri.
     */
    public function execute(GalleryCategory $category): void
    {
        $old = $category->only(['name', 'description']);
        $category->delete();
        app(AuditLogger::class)->log('delete', $category, $old, []);
    }
}
