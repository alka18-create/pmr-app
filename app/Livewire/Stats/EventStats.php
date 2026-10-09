<?php

namespace App\Livewire\Stats;

use App\Services\Reports\EventStatistics;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class EventStats extends Component
{
    public int $year;

    public function mount(): void
    {
        Gate::authorize('access-pengurus-area');
        $this->year = (int) now()->year;
    }

    public function updatedYear(): void
    {
        $this->year = (int) $this->year;
    }

    public function render(EventStatistics $statistics)
    {
        return view('livewire.stats.event-stats', [
            'stats' => $statistics->build($this->year),
        ]);
    }
}
