<?php

namespace App\Livewire\Website;

use App\Enums\AchievementLevel;
use App\Models\Achievement;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.public', ['title' => 'Prestasi'])]
class AchievementDirectory extends Component
{
    use WithPagination;

    public string $levelFilter = '';

    public string $yearFilter = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['levelFilter', 'yearFilter'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $achievements = Achievement::query()
            ->where('is_public', true)
            ->when($this->levelFilter !== '', fn ($q) => $q->where('level', $this->levelFilter))
            ->when($this->yearFilter !== '', fn ($q) => $q->where('year', $this->yearFilter))
            ->orderByDesc('year')
            ->orderByDesc('id')
            ->paginate(12);

        $years = Achievement::query()
            ->where('is_public', true)
            ->orderByDesc('year')
            ->distinct()
            ->pluck('year');

        return view('livewire.website.achievement-directory', [
            'achievements' => $achievements,
            'levels' => AchievementLevel::cases(),
            'years' => $years,
        ]);
    }
}
