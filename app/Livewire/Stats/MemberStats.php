<?php

namespace App\Livewire\Stats;

use App\Services\Reports\MemberStatistics;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class MemberStats extends Component
{
    public function mount(): void
    {
        Gate::authorize('access-pengurus-area');
    }

    public function render(MemberStatistics $statistics)
    {
        return view('livewire.stats.member-stats', [
            'stats' => $statistics->build(),
        ]);
    }
}
