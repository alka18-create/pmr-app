<?php

namespace App\Actions\Quizzes;

use App\Enums\QuizAttemptStatus;
use App\Models\QuizAttempt;

/**
 * Menandai attempt quiz yang melewati batas waktu sebagai `expired`.
 * Dijalankan terjadwal (routes/console.php) sebagai pelengkap cek "lazy"
 * saat mount/submit.
 */
class ExpireStaleQuizAttempts
{
    public function execute(): int
    {
        $expired = 0;

        QuizAttempt::query()
            ->where('status', QuizAttemptStatus::Running)
            ->whereHas('quiz', fn ($q) => $q->whereNotNull('time_limit_minutes'))
            ->with('quiz:id,time_limit_minutes')
            ->chunkById(100, function ($attempts) use (&$expired) {
                foreach ($attempts as $attempt) {
                    if ($attempt->isExpired()) {
                        $attempt->update([
                            'status' => QuizAttemptStatus::Expired,
                            'finished_at' => now(),
                        ]);
                        $expired++;
                    }
                }
            });

        return $expired;
    }
}
