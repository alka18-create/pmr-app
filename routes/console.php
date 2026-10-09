<?php

use App\Actions\Quizzes\ExpireStaleQuizAttempts;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(fn () => app(ExpireStaleQuizAttempts::class)->execute())
    ->name('expire-stale-quiz-attempts')
    ->everyFiveMinutes()
    ->withoutOverlapping();
