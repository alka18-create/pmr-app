<?php

namespace App\Actions\Events;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Notifications\EventPublishedNotification;
use App\Services\AuditLogger;
use App\Services\Notifier;

class UpdateEvent
{
    public function execute(Event $event, array $data): Event
    {
        $old = $event->getAttributes();
        $event->update($data);

        app(AuditLogger::class)->log(
            'updated',
            $event,
            $old,
            $event->getChanges(),
        );

        if ($event->wasChanged('status') && $event->status === EventStatus::Published) {
            app(Notifier::class)->toActiveMembers(new EventPublishedNotification(
                $event->title,
                $event->slug,
                $event->starts_at?->format('d M Y H:i'),
            ));
        }

        return $event;
    }
}
