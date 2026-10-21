<?php

namespace App\Actions\Gallery;

use App\Models\GalleryCategory;
use App\Services\AuditLogger;

class SaveGalleryCategory
{
    /**
     * Buat atau perbarui kategori galeri.
     *
     * @param  array{name: string, description?: ?string}  $data
     */
    public function execute(array $data, ?int $id = null): GalleryCategory
    {
        if ($id) {
            $category = GalleryCategory::findOrFail($id);
            $old = $category->only(['name', 'description']);
            $category->update($data);
            app(AuditLogger::class)->log('update', $category, $old, $category->only(['name', 'description']));

            return $category;
        }

        $category = GalleryCategory::create($data);
        app(AuditLogger::class)->log('create', $category, [], $category->only(['name', 'description']));

        return $category;
    }
}
