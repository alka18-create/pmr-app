<?php

namespace App\Actions\Quizzes;

use App\Enums\QuizAttemptStatus;
use App\Models\QuizAttempt;
use Illuminate\Validation\ValidationException;

class GradeQuizAttempt
{
    /**
     * Hitung skor dari jawaban tersimpan (setelah SubmitQuizAttempt menyimpan jawaban).
     * Hanya attempt berstatus finished yang boleh di-grade ulang untuk perhitungan ulang.
     */
    public function execute(QuizAttempt $attempt): QuizAttempt
    {
        if ($attempt->status !== QuizAttemptStatus::Finished) {
            throw ValidationException::withMessages([
                'quiz' => 'Hanya attempt yang sudah selesai yang bisa dinilai.',
            ]);
        }

        $totalScore = 0;
        $awardedTotal = 0;

        foreach ($attempt->answers()->with('question')->get() as $answer) {
            $totalScore += $answer->question->score;
            $awardedTotal += $answer->awarded_score;
        }

        $score = $totalScore > 0
            ? (int) round(($awardedTotal / $totalScore) * 100)
            : 0;

        $attempt->update(['score' => $score]);

        return $attempt->refresh();
    }
}
