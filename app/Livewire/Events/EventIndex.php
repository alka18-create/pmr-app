<?php

namespace App\Livewire\Events;

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.panel')]
class EventIndex extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $search = '';

    public string $typeFilter = '';

    public string $statusFilter = '';

    public function mount(): void
    {
        $this->authorize('create', Event::class);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'typeFilter', 'statusFilter'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $events = Event::query()
            ->withCount('participants')
            ->when($this->search !== '', function ($query) {
                $term = '%'.mb_strtolower($this->search).'%';
                $query->whereRaw('LOWER(title) LIKE ?', [$term]);
            })
            ->when($this->typeFilter !== '', fn ($query) => $query->where('type', $this->typeFilter))
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->orderByDesc('starts_at')
            ->paginate(10);

        return view('livewire.events.event-index', [
            'events' => $events,
            'types' => EventType::cases(),
            'statuses' => EventStatus::cases(),
        ]);
    }
}
