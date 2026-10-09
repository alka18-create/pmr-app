<?php

namespace App\Enums;

enum MaterialCategory: string
{
    case DasarP3k = 'dasar_p3k';
    case Keorganisasian = 'keorganisasian';
    case Kesehatan = 'kesehatan';
    case Kebencanaan = 'kebencanaan';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::DasarP3k => 'Dasar P3K',
            self::Keorganisasian => 'Keorganisasian',
            self::Kesehatan => 'Kesehatan',
            self::Kebencanaan => 'Kebencanaan',
            self::Lainnya => 'Lainnya',
        };
    }
}
