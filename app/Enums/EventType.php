<?php

namespace App\Enums;

enum EventType: string
{
    case BaktiSosial = 'bakti_sosial';
    case DonorDarah = 'donor_darah';
    case Pelatihan = 'pelatihan';
    case LatihanRutin = 'latihan_rutin';
    case Lainnya = 'lainnya';
}
