<?php

namespace App\Enums;

enum MemberStatus: string
{
    case Aktif = 'aktif';
    case Alumni = 'alumni';
    case Nonaktif = 'nonaktif';
}
