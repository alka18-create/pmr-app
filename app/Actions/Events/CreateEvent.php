<?php

namespace App\Actions\Events;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Notifications\EventPublishedNotification;
use App\Services\AuditLogger;
use App\Services\Notifier;
use Illuminate\Support\Arr;

class CreateEvent
{
    public function execute(array $data): Event
    {
        $event = Event::create($data);

        app(AuditLogger::class)->log(
            'created',
            $event,
            [],
            Arr::except($event->getAttributes(), []),
        );

        if ($event->status === EventStatus::Published) {
            app(Notifier::class)->toActiveMembers(new EventPublishedNotification(
                $event->title,
                $event->slug,
                $event->starts_at?->format('d M Y H:i'),
            ));
        }

        return $event;
    }
}
