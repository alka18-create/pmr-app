<?php

namespace App\Livewire\Quizzes;

use App\Actions\Quizzes\StartQuizAttempt;
use App\Actions\Quizzes\SubmitQuizAttempt;
use App\Enums\QuizAttemptStatus;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class QuizTake extends Component
{
    use AuthorizesRequests;

    #[Locked]
    public int $quizId;

    #[Locked]
    public int $attemptId = 0;

    /** @var array<int|string, int|string|null> */
    public array $answers = [];

    public ?int $secondsRemaining = null;

    #[Locked]
    public bool $submitted = false;

    #[Locked]
    public ?int $finalScore = null;

    #[Locked]
    public ?bool $passed = null;

    public function mount(Quiz $quiz): void
    {
        $quiz->load('questions.options');
        $this->authorize('attempt', $quiz);

        $member = auth()->user()->member;

        if (! $member) {
            abort(403, 'Akun Anda belum terhubung ke profil anggota.');
        }

        $attempt = (new StartQuizAttempt)->execute(
            $quiz,
            $member->id,
            request()->ip(),
        );

        $this->quizId = $quiz->id;
        $this->attemptId = $attempt->id;
        $this->secondsRemaining = $attempt->secondsRemaining();
    }

    public function submit(SubmitQuizAttempt $submitQuizAttempt)
    {
        $quiz = Quiz::findOrFail($this->quizId);
        $attempt = QuizAttempt::findOrFail($this->attemptId);
        $this->authorize('attempt', $quiz);

        if ($this->answers === []) {
            throw ValidationException::withMessages([
                'answers' => 'Jawab semua soal sebelum mengumpulkan.',
            ]);
        }

        foreach ($quiz->questions as $question) {
            if (! isset($this->answers[$question->id]) || $this->answers[$question->id] === null) {
                throw ValidationException::withMessages([
                    'answers' => 'Jawab semua soal sebelum mengumpulkan.',
                ]);
            }
        }

        $attempt = $submitQuizAttempt->execute($attempt, $this->answers);

        $this->submitted = true;
        $this->finalScore = $attempt->score;
        $this->passed = $attempt->score !== null && $attempt->score >= $quiz->pass_score;
        $this->secondsRemaining = null;

        return redirect()->route('results.index');
    }

    public function render()
    {
        $quiz = Quiz::with('questions.options')->findOrFail($this->quizId);
        $attempt = QuizAttempt::findOrFail($this->attemptId);

        if ($attempt->status === QuizAttemptStatus::Running) {
            $this->secondsRemaining = $attempt->secondsRemaining();
        }

        $initialSeconds = ($attempt->status === QuizAttemptStatus::Running && $this->secondsRemaining !== null)
            ? (int) $this->secondsRemaining
            : 0;

        return view('livewire.quizzes.quiz-take', [
            'quiz' => $quiz,
            'attempt' => $attempt,
            'initialSeconds' => $initialSeconds,
        ]);
    }
}
