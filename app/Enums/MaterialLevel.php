<?php

namespace App\Enums;

enum MaterialLevel: string
{
    case Mula = 'mula';
    case Madya = 'madya';
    case Wira = 'wira';

    public function label(): string
    {
        return match ($this) {
            self::Mula => 'Mula',
            self::Madya => 'Madya',
            self::Wira => 'Wira',
        };
    }
}
