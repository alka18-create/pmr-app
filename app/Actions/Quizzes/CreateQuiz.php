<?php

namespace App\Actions\Quizzes;

use App\Models\Quiz;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class CreateQuiz
{
    /**
     * @param  array<string, mixed>  $data
     * @param  list<array{question: string, score: int, options: list<array{text: string, is_correct: bool}>}>  $questions
     */
    public function execute(array $data, array $questions): Quiz
    {
        $quiz = DB::transaction(function () use ($data, $questions) {
            $quiz = Quiz::create($data);

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

            return $quiz;
        });

        app(AuditLogger::class)->log(
            'created',
            $quiz,
            [],
            $quiz->getAttributes(),
        );

        return $quiz;
    }
}
