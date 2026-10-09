<?php

namespace App\Actions\Events;

use App\Models\EventParticipant;
use Illuminate\Support\Str;

class GenerateParticipantQrToken
{
    public function execute(EventParticipant $participant): EventParticipant
    {
        $participant->qr_token = (string) Str::uuid();
        $participant->save();

        return $participant;
    }
}
