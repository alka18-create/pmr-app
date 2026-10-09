<?php

namespace App\Enums\Auth;

enum LoginResult: string
{
    case Success = 'success';
    case Failed = 'failed';
    case Throttled = 'throttled';
}
