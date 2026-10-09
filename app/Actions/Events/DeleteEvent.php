<?php

namespace App\Actions\Events;

use App\Models\Event;
use App\Services\AuditLogger;

class DeleteEvent
{
    public function execute(Event $event): void
    {
        $old = $event->getAttributes();
        $event->delete();

        app(AuditLogger::class)->log('deleted', $event, $old, []);
    }
}
