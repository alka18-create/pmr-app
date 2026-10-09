<?php

namespace App\Enums;

enum AttendanceMethod: string
{
    case Manual = 'manual';
    case Qr = 'qr';
}
