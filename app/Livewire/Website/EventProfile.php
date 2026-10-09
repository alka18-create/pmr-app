<?php

namespace App\Livewire\Website;

use App\Models\Event;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.public', ['title' => 'Detail Kegiatan'])]
class EventProfile extends Component
{
    #[Locked]
    public string $slug = '';

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    public function render()
    {
        $event = Event::query()
            ->where('slug', $this->slug)
            ->where('is_public', true)
            ->whereIn('status', ['published', 'selesai'])
            ->firstOrFail();

        $documents = $event->documents()
            ->where('is_public', true)
            ->get(['file_path', 'file_type', 'caption'])
            ->toArray();

        return view('livewire.website.event-profile', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'slug' => $event->slug,
                'type' => $event->type->value,
                'description' => $event->description,
                'location' => $event->location,
                'starts_at' => $event->starts_at->format('d M Y H:i'),
                'status' => $event->status->value,
                'documents' => $documents,
            ],
        ]);
    }
}
