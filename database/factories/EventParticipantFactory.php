<?php

namespace Database\Factories;

use App\Enums\ParticipantRole;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EventParticipant>
 */
class EventParticipantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'member_id' => Member::factory(),
            'role_in_event' => ParticipantRole::Peserta,
            'qr_token' => (string) Str::uuid(),
            'invited_at' => now(),
        ];
    }
}
