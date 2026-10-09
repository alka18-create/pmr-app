<?php

namespace App\Livewire\Notifications;

use Livewire\Component;

class NotificationsBadge extends Component
{
    public function render()
    {
        $count = auth()->check()
            ? auth()->user()->unreadNotifications()->count()
            : 0;

        return view('livewire.notifications.notifications-badge', [
            'count' => $count,
        ]);
    }
}
