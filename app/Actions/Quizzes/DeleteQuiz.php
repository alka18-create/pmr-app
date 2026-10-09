<?php

namespace App\Actions\Quizzes;

use App\Models\Quiz;
use App\Services\AuditLogger;

class DeleteQuiz
{
    public function execute(Quiz $quiz): void
    {
        app(AuditLogger::class)->log(
            'deleted',
            $quiz,
            $quiz->getAttributes(),
            [],
        );

        $quiz->delete();
    }
}
