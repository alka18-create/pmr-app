<?php

namespace App\Actions\Events;

use App\Models\Event;
use App\Models\EventParticipant;
use App\Services\AuditLogger;

class RemoveEventParticipant
{
    /**
     * Hapus peserta dari kegiatan.
     */
    public function execute(Event $event, int $participantId): bool
    {
        $participant = EventParticipant::where('event_id', $event->id)
            ->where('id', $participantId)
            ->first();

        if (! $participant) {
            return false;
        }

        $old = $participant->only(['event_id', 'member_id', 'role_in_event']);
        $participant->delete();

        app(AuditLogger::class)->log('delete', $participant, $old, []);

        return true;
    }
}
