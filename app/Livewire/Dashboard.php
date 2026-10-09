<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\Member;
use App\Services\Reports\EventStatistics;
use App\Services\Reports\MemberStatistics;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class Dashboard extends Component
{
    public function render(MemberStatistics $members, EventStatistics $events)
    {
        $canManage = auth()->user()->can('create', Member::class);

        return view('livewire.dashboard', [
            'totalMembers' => $members->total(),
            'totalEvents' => $events->total(),
            'upcomingEvents' => Event::query()
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->limit(5)
                ->get(['id', 'title', 'starts_at']),
            'memberStats' => $canManage ? $members->build() : null,
            'recentMembers' => $canManage
                ? Member::orderByDesc('id')->limit(5)->get(['id', 'full_name'])
                : collect(),
        ]);
    }
}
