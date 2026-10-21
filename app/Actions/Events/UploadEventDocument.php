<?php

namespace App\Actions\Events;

use App\Models\Event;
use App\Models\EventDocument;
use App\Services\AuditLogger;
use Illuminate\Http\UploadedFile;

class UploadEventDocument
{
    /**
     * Upload dan simpan dokumentasi kegiatan.
     *
     * @param  array{caption?: ?string, is_public: bool, uploaded_by: ?int}  $attributes
     */
    public function execute(Event $event, UploadedFile $file, array $attributes): EventDocument
    {
        $path = $file->store('events/'.$event->id, 'public');
        $extension = strtolower($file->getClientOriginalExtension());
        $type = in_array($extension, ['mp4', 'mov', 'avi'], true) ? 'video' : 'image';

        $document = EventDocument::create([
            'event_id' => $event->id,
            'file_path' => $path,
            'file_type' => $type,
            'caption' => $attributes['caption'] ?? null,
            'is_public' => (bool) ($attributes['is_public'] ?? false),
            'uploaded_by' => $attributes['uploaded_by'] ?? auth()->id(),
        ]);

        app(AuditLogger::class)->log('create', $document, [], [
            'event_id' => $event->id,
            'file_path' => $path,
            'file_type' => $type,
            'is_public' => $document->is_public,
        ]);

        return $document;
    }
}
