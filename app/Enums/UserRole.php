<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Pengurus = 'pengurus';
    case Anggota = 'anggota';
}
