<?php

namespace App\Livewire\Notifications;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Selalu scoped ke auth()->user() — tidak perlu policy tersendiri.
 */
#[Layout('components.layouts.panel')]
class NotificationsIndex extends Component
{
    use WithPagination;

    public function markRead(string $id): void
    {
        auth()->user()
            ->notifications()
            ->where('id', $id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function markAllRead(): void
    {
        auth()->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);
    }

    public function render()
    {
        return view('livewire.notifications.notifications-index', [
            'notifications' => auth()->user()->notifications()->paginate(15),
            'unreadCount' => auth()->user()->unreadNotifications()->count(),
        ]);
    }
}
