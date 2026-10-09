<?php

namespace App\Livewire\Quizzes;

use App\Actions\Quizzes\CreateQuiz;
use App\Models\Material;
use App\Models\Quiz;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class QuizCreate extends Component
{
    use AuthorizesRequests;

    public string $title = '';

    public string $description = '';

    public $material_id = null;

    public $time_limit_minutes = 30;

    public int $max_attempts = 3;

    public int $pass_score = 70;

    public bool $is_active = true;

    public $starts_at = '';

    public $ends_at = '';

    /** @var list<array{question: string, score: int, options: list<array{text: string, is_correct: bool}>}> */
    public array $questions = [];

    public function mount(): void
    {
        $this->authorize('manage', Quiz::class);
        $this->starts_at = now()->format('Y-m-d\TH:i');
        $this->ends_at = now()->addMonth()->format('Y-m-d\TH:i');
        $this->addQuestion();
    }

    public function addQuestion(): void
    {
        $this->questions[] = [
            'question' => '',
            'score' => 10,
            'options' => [
                ['text' => '', 'is_correct' => true],
                ['text' => '', 'is_correct' => false],
                ['text' => '', 'is_correct' => false],
                ['text' => '', 'is_correct' => false],
            ],
        ];
    }

    public function removeQuestion(int $index): void
    {
        unset($this->questions[$index]);
        $this->questions = array_values($this->questions);

        if ($this->questions === []) {
            $this->addQuestion();
        }
    }

    public function addOption(int $questionIndex): void
    {
        $this->questions[$questionIndex]['options'][] = [
            'text' => '',
            'is_correct' => false,
        ];
    }

    public function removeOption(int $questionIndex, int $optionIndex): void
    {
        if (count($this->questions[$questionIndex]['options']) <= 2) {
            return;
        }

        unset($this->questions[$questionIndex]['options'][$optionIndex]);
        $this->questions[$questionIndex]['options'] = array_values($this->questions[$questionIndex]['options']);
    }

    public function setCorrect(int $questionIndex, int $optionIndex): void
    {
        foreach ($this->questions[$questionIndex]['options'] as $i => $_) {
            $this->questions[$questionIndex]['options'][$i]['is_correct'] = $i === $optionIndex;
        }
    }

    public function save(CreateQuiz $createQuiz)
    {
        $rules = [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'material_id' => ['nullable', 'integer', 'exists:materials,id'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1', 'max:480'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:10'],
            'pass_score' => ['required', 'integer', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question' => ['required', 'string', 'max:2000'],
            'questions.*.score' => ['required', 'integer', 'min:1', 'max:100'],
            'questions.*.options' => ['required', 'array', 'min:2'],
            'questions.*.options.*.text' => ['required', 'string', 'max:500'],
            'questions.*.options.*.is_correct' => ['boolean'],
        ];

        $this->validate($rules, [
            'title.required' => 'Judul quiz wajib diisi.',
            'max_attempts.required' => 'Batas attempt wajib diisi.',
            'pass_score.required' => 'Nilai kelulusan wajib diisi.',
            'ends_at.after_or_equal' => 'Waktu selesai tidak boleh sebelum waktu mulai.',
            'questions.required' => 'Minimal satu soal wajib diisi.',
            'questions.*.question.required' => 'Pertanyaan wajib diisi.',
            'questions.*.options.*.text.required' => 'Setiap opsi wajib diisi.',
        ]);

        foreach ($this->questions as $i => $question) {
            $correctCount = collect($question['options'])->where('is_correct', true)->count();
            if ($correctCount !== 1) {
                $this->addError("questions.$i", 'Setiap soal harus punya tepat satu jawaban benar.');

                return;
            }
        }

        $data = [
            'title' => $this->title,
            'description' => $this->description ?: null,
            'material_id' => $this->material_id ?: null,
            'time_limit_minutes' => $this->time_limit_minutes !== null && $this->time_limit_minutes !== ''
                ? (int) $this->time_limit_minutes
                : null,
            'max_attempts' => $this->max_attempts,
            'pass_score' => $this->pass_score,
            'is_active' => (bool) $this->is_active,
            'starts_at' => $this->starts_at ?: null,
            'ends_at' => $this->ends_at ?: null,
            'created_by' => auth()->id(),
        ];

        $createQuiz->execute($data, $this->questions);

        session()->flash('success', 'Quiz berhasil dibuat.');

        return redirect()->route('quizzes.index');
    }

    public function render()
    {
        return view('livewire.quizzes.quiz-create', [
            'materials' => Material::orderBy('title')->get(['id', 'title']),
        ]);
    }
}
