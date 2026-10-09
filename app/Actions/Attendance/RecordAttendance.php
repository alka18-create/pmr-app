<?php

namespace App\Actions\Attendance;

use App\Enums\AttendanceMethod;
use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\Member;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class RecordAttendance
{
    /**
     * @param  array{status: string, notes?: string|null, method?: string, checked_in_by?: int|null}  $data
     */
    public function execute(Event $event, Member $member, array $data): AttendanceRecord
    {
        return DB::transaction(function () use ($event, $member, $data) {
            $status = AttendanceStatus::from($data['status']);
            $method = isset($data['method'])
                ? AttendanceMethod::from($data['method'])
                : AttendanceMethod::Manual;

            $record = AttendanceRecord::updateOrCreate(
                ['event_id' => $event->id, 'member_id' => $member->id],
                [
                    'status' => $status,
                    'method' => $method,
                    'checked_in_at' => $status === AttendanceStatus::Hadir ? now() : ($data['checked_in_at'] ?? null),
                    'checked_in_by' => $data['checked_in_by'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ],
            );

            app(AuditLogger::class)->log(
                'updated',
                $record,
                [],
                $record->getAttributes(),
            );

            return $record;
        });
    }
}
