<?php

namespace App\Enums;

enum QuizAttemptStatus: string
{
    case Running = 'running';
    case Finished = 'finished';
    case Expired = 'expired';
}
