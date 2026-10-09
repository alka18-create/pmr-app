<?php

namespace App\Livewire\Website;

use App\Models\Event;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.public', ['title' => 'Kegiatan'])]
class EventDirectory extends Component
{
    use WithPagination;

    public function render()
    {
        $events = Event::query()
            ->where('is_public', true)
            ->whereIn('status', ['published', 'selesai'])
            ->orderByDesc('starts_at')
            ->paginate(12);

        return view('livewire.website.event-directory', [
            'events' => $events,
        ]);
    }
}
