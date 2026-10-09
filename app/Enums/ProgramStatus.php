<?php

namespace App\Enums;

enum ProgramStatus: string
{
    case Rencana = 'rencana';
    case Berjalan = 'berjalan';
    case Selesai = 'selesai';
}
