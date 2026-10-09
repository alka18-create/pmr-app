<?php

namespace App\Livewire\Quizzes;

use App\Models\Certificate;
use App\Models\QuizAttempt;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class ResultShow extends Component
{
    use AuthorizesRequests;

    public function mount(): void
    {
        if (! auth()->user()) {
            abort(401);
        }
    }

    public function render()
    {
        $user = auth()->user();
        $member = $user->member;

        $attempts = $member
            ? QuizAttempt::with(['quiz:id,title,pass_score'])
                ->where('member_id', $member->id)
                ->orderByDesc('id')
                ->get()
            : collect();

        $certificates = $member
            ? Certificate::where('member_id', $member->id)
                ->where('is_revoked', false)
                ->orderByDesc('id')
                ->get()
            : collect();

        return view('livewire.quizzes.result-show', [
            'attempts' => $attempts,
            'certificates' => $certificates,
            'member' => $member,
        ]);
    }
}
