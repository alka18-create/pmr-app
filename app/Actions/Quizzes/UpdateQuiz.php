<?php

namespace App\Actions\Quizzes;

use App\Models\Quiz;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class UpdateQuiz
{
    /**
     * @param  array<string, mixed>  $data
     * @param  list<array{question: string, score: int, options: list<array{text: string, is_correct: bool}>}>|null  $questions
     */
    public function execute(Quiz $quiz, array $data, ?array $questions = null): Quiz
    {
        $old = $quiz->getAttributes();

        DB::transaction(function () use ($quiz, $data, $questions) {
            $quiz->update($data);

            if ($questions !== null) {
                $quiz->questions()->delete();

                foreach ($questions as $index => $questionData) {
                    $question = $quiz->questions()->create([
                        'question' => $questionData['question'],
                        'type' => 'multiple_choice',
                        'score' => (int) $questionData['score'],
                        'order' => $index,
                    ]);

                    foreach ($questionData['options'] as $optionIndex => $optionData) {
                        $question->options()->create([
                            'option_text' => $optionData['text'],
                            'is_correct' => (bool) $optionData['is_correct'],
                            'order' => $optionIndex,
                        ]);
                    }
                }
            }
        });

        app(AuditLogger::class)->log(
            'updated',
            $quiz,
            $old,
            $quiz->getChanges(),
        );

        return $quiz->refresh();
    }
}
