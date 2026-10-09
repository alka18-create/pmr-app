<?php

namespace App\Actions\Quizzes;

use App\Enums\QuizAttemptStatus;
use App\Models\QuizAttempt;
use App\Notifications\QuizResultPublishedNotification;
use App\Services\AuditLogger;
use App\Services\Notifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmitQuizAttempt
{
    /**
     * @param  array<int|string, int|string|null>  $answers  question_id => option_id
     */
    public function execute(QuizAttempt $attempt, array $answers): QuizAttempt
    {
        if ($attempt->status !== QuizAttemptStatus::Running) {
            throw ValidationException::withMessages([
                'quiz' => 'Attempt ini sudah ditutup.',
            ]);
        }

        if ($attempt->isExpired()) {
            $attempt->update([
                'status' => QuizAttemptStatus::Expired,
                'finished_at' => now(),
            ]);

            app(AuditLogger::class)->log(
                'updated',
                $attempt,
                ['status' => QuizAttemptStatus::Running->value],
                ['status' => QuizAttemptStatus::Expired->value],
            );

            throw ValidationException::withMessages([
                'quiz' => 'Waktu quiz sudah habis.',
            ]);
        }

        return DB::transaction(function () use ($attempt, $answers) {
            $attempt->answers()->delete();

            $totalScore = 0;
            $awardedTotal = 0;

            $questions = $attempt->quiz->questions()->with('options')->get();

            foreach ($questions as $question) {
                $selectedOptionId = $answers[$question->id] ?? null;
                $option = $question->options->firstWhere('id', $selectedOptionId);
                $isCorrect = (bool) $option?->is_correct;
                $awarded = $isCorrect ? $question->score : 0;

                $totalScore += $question->score;
                $awardedTotal += $awarded;

                $attempt->answers()->create([
                    'question_id' => $question->id,
                    'option_id' => $option?->id,
                    'is_correct' => $isCorrect,
                    'awarded_score' => $awarded,
                ]);
            }

            $score = $totalScore > 0
                ? (int) round(($awardedTotal / $totalScore) * 100)
                : 0;

            $attempt->update([
                'status' => QuizAttemptStatus::Finished,
                'finished_at' => now(),
                'score' => $score,
            ]);

            app(AuditLogger::class)->log(
                'updated',
                $attempt,
                ['status' => QuizAttemptStatus::Running->value],
                ['status' => QuizAttemptStatus::Finished->value, 'score' => $score],
            );

            app(Notifier::class)->toMember(
                $attempt->member,
                new QuizResultPublishedNotification(
                    $attempt->quiz->title,
                    $score,
                    $score >= $attempt->quiz->pass_score,
                ),
            );

            if ($score >= $attempt->quiz->pass_score) {
                (new IssueCertificateForAttempt)->execute($attempt);
            }

            return $attempt->refresh();
        });
    }
}
