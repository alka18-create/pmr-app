<?php

namespace App\Enums;

enum CertificateSourceType: string
{
    case Quiz = 'quiz';
    case Event = 'event';
    case Training = 'training';

    public function label(): string
    {
        return match ($this) {
            self::Quiz => 'Quiz',
            self::Event => 'Kegiatan',
            self::Training => 'Pelatihan',
        };
    }
}
