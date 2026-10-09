<?php

namespace App\Livewire\Quizzes;

use App\Enums\MaterialCategory;
use App\Models\Quiz;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.panel')]
class QuizIndex extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $search = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Quiz::class);
    }

    public function render()
    {
        $canManage = auth()->user()?->can('manage', Quiz::class) ?? false;

        $quizzes = Quiz::query()
            ->with('material:id,title,category,level')
            ->when($this->search !== '', function ($query) {
                $term = '%'.mb_strtolower($this->search).'%';
                $query->whereRaw('LOWER(title) LIKE ?', [$term]);
            })
            ->when(! $canManage, fn ($query) => $query->where('is_active', true))
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.quizzes.quiz-index', [
            'quizzes' => $quizzes,
            'canManage' => $canManage,
            'categories' => MaterialCategory::cases(),
        ]);
    }
}
