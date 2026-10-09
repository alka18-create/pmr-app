<?php

namespace App\Actions\Quizzes;

use App\Enums\QuizAttemptStatus;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Validation\ValidationException;

class StartQuizAttempt
{
    public function execute(Quiz $quiz, int $memberId, ?string $ip = null): QuizAttempt
    {
        $running = $quiz->attempts()
            ->where('member_id', $memberId)
            ->where('status', QuizAttemptStatus::Running)
            ->first();

        if ($running) {
            if ($running->isExpired()) {
                $running->update([
                    'status' => QuizAttemptStatus::Expired,
                    'finished_at' => now(),
                ]);
            } else {
                return $running;
            }
        }

        $usedAttempts = $quiz->attempts()
            ->where('member_id', $memberId)
            ->whereIn('status', [QuizAttemptStatus::Finished, QuizAttemptStatus::Expired])
            ->count();

        if ($usedAttempts >= $quiz->max_attempts) {
            throw ValidationException::withMessages([
                'quiz' => 'Batas percobaan quiz sudah habis.',
            ]);
        }

        $nextAttemptNo = (int) $quiz->attempts()
            ->where('member_id', $memberId)
            ->max('attempt_no') + 1;

        return $quiz->attempts()->create([
            'member_id' => $memberId,
            'attempt_no' => $nextAttemptNo,
            'started_at' => now(),
            'status' => QuizAttemptStatus::Running,
            'ip' => $ip,
        ]);
    }
}
