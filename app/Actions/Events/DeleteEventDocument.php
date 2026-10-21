<?php

namespace App\Actions\Events;

use App\Models\EventDocument;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Storage;

class DeleteEventDocument
{
    /**
     * Hapus dokumen kegiatan beserta file fisiknya.
     */
    public function execute(EventDocument $document): void
    {
        $old = $document->only(['event_id', 'file_path', 'caption', 'is_public']);

        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        app(AuditLogger::class)->log('delete', $document, $old, []);
    }
}
