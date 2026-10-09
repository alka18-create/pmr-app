<?php

namespace App\Enums;

enum EventStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Selesai = 'selesai';
    case Batal = 'batal';
}
