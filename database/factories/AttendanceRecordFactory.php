<?php

namespace Database\Factories;

use App\Enums\AttendanceMethod;
use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceRecord>
 */
class AttendanceRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'member_id' => Member::factory(),
            'status' => AttendanceStatus::Hadir,
            'checked_in_at' => now(),
            'checked_in_by' => User::factory(),
            'method' => AttendanceMethod::Manual,
            'notes' => null,
        ];
    }
}
