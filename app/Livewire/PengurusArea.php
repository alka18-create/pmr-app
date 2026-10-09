<?php

namespace App\Livewire;

use App\Enums\EventStatus;
use App\Enums\MemberStatus;
use App\Enums\MessageStatus;
use App\Models\Event;
use App\Models\Member;
use App\Models\Message;
use App\Models\Quiz;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Ringkasan operasional pengurus (pengganti placeholder panel.area).
 * Route sudah diproteksi middleware role:admin|pengurus.
 */
class PengurusArea extends Component
{
    #[Layout('components.layouts.panel')]
    public function render()
    {
        return view('livewire.pengurus-area', [
            'activeMembers' => Member::query()->where('status', MemberStatus::Aktif->value)->count(),
            'upcomingCount' => Event::query()->where('starts_at', '>=', now())->count(),
            'unreadMessages' => Message::query()->where('status', MessageStatus::Unread->value)->count(),
            'activeQuizzes' => Quiz::query()->where('is_active', true)->count(),
            'upcomingEvents' => Event::query()
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->limit(5)
                ->get(['id', 'title', 'starts_at', 'location']),
            'draftEvents' => Event::query()
                ->where('status', EventStatus::Draft->value)
                ->orderByDesc('id')
                ->limit(5)
                ->get(['id', 'title', 'starts_at']),
            'latestMessages' => Message::query()
                ->orderByDesc('id')
                ->limit(5)
                ->get(['id', 'name', 'subject', 'status', 'created_at']),
        ]);
    }
}
