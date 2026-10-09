<?php

namespace App\Enums;

enum MessageStatus: string
{
    case Unread = 'unread';
    case Read = 'read';
    case Replied = 'replied';
}
