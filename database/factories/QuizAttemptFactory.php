<?php

namespace Database\Factories;

use App\Enums\QuizAttemptStatus;
use App\Models\Member;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizAttempt>
 */
class QuizAttemptFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'member_id' => Member::factory(),
            'attempt_no' => 1,
            'started_at' => now(),
            'finished_at' => null,
            'score' => null,
            'status' => QuizAttemptStatus::Running,
            'ip' => '127.0.0.1',
        ];
    }

    public function finished(int $score = 80): static
    {
        return $this->state(fn () => [
            'status' => QuizAttemptStatus::Finished,
            'finished_at' => now(),
            'score' => $score,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => QuizAttemptStatus::Expired,
            'finished_at' => now(),
            'score' => null,
        ]);
    }
}
