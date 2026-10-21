<?php

namespace App\Actions\Events;

use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\Member;
use App\Services\AuditLogger;

class AddEventParticipant
{
    /**
     * Tambahkan 1 anggota sebagai peserta kegiatan.
     */
    public function execute(Event $event, Member $member, string $roleInEvent = 'peserta'): ?EventParticipant
    {
        $exists = EventParticipant::where('event_id', $event->id)
            ->where('member_id', $member->id)
            ->exists();

        if ($exists) {
            return null;
        }

        $participant = EventParticipant::create([
            'event_id' => $event->id,
            'member_id' => $member->id,
            'role_in_event' => $roleInEvent,
            'invited_at' => now(),
        ]);

        app(AuditLogger::class)->log('create', $participant, [], [
            'event_id' => $event->id,
            'member_id' => $member->id,
            'role_in_event' => $roleInEvent,
        ]);

        return $participant;
    }

    /**
     * Tambahkan seluruh anggota dalam kelas ke kegiatan.
     *
     * @return int Jumlah peserta baru yang ditambahkan
     */
    public function bulkByClass(Event $event, int $classId): int
    {
        $memberIds = Member::where('class_id', $classId)->pluck('id');
        if ($memberIds->isEmpty()) {
            return 0;
        }

        $existing = EventParticipant::where('event_id', $event->id)
            ->whereIn('member_id', $memberIds)
            ->pluck('member_id');

        $added = 0;
        foreach ($memberIds->diff($existing) as $memberId) {
            $participant = EventParticipant::create([
                'event_id' => $event->id,
                'member_id' => $memberId,
                'role_in_event' => 'peserta',
                'invited_at' => now(),
            ]);

            app(AuditLogger::class)->log('create', $participant, [], [
                'event_id' => $event->id,
                'member_id' => $memberId,
                'role_in_event' => 'peserta',
            ]);

            $added++;
        }

        return $added;
    }
}
