<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceMethod;
use App\Enums\AttendanceStatus;
use App\Enums\EventStatus;
use App\Models\AttendanceRecord;
use App\Models\EventParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class CheckInController extends Controller
{
    public function __invoke(Request $request, string $token)
    {
        $limiterKey = 'check-in:'.($request->ip() ?? 'unknown');

        if (RateLimiter::tooManyAttempts($limiterKey, 10)) {
            $seconds = RateLimiter::availableIn($limiterKey);

            return response()->view('check-in.result', [
                'success' => false,
                'message' => 'Terlalu banyak percobaan. Coba lagi dalam '.$seconds.' detik.',
            ], 429)->header('Retry-After', (string) $seconds);
        }

        RateLimiter::hit($limiterKey, 60);

        $participant = EventParticipant::with(['event', 'member:id,full_name,slug'])
            ->where('qr_token', $token)
            ->first();

        if (! $participant) {
            return response()->view('check-in.result', [
                'success' => false,
                'message' => 'Token tidak ditemukan.',
            ], 404);
        }

        $event = $participant->event;

        if (! $event || in_array($event->status, [EventStatus::Draft, EventStatus::Batal], true)) {
            return response()->view('check-in.result', [
                'success' => false,
                'message' => 'Kegiatan tidak aktif.',
            ], 422);
        }

        if ($event->ends_at && $event->ends_at->isPast()) {
            return response()->view('check-in.result', [
                'success' => false,
                'message' => 'Kegiatan sudah berakhir.',
            ], 422);
        }

        $existing = AttendanceRecord::where('event_id', $event->id)
            ->where('member_id', $participant->member_id)
            ->first();

        if ($existing && $existing->status === AttendanceStatus::Hadir) {
            return response()->view('check-in.result', [
                'success' => false,
                'message' => 'Anda sudah check-in untuk kegiatan ini.',
            ], 409);
        }

        AttendanceRecord::updateOrCreate(
            ['event_id' => $event->id, 'member_id' => $participant->member_id],
            [
                'status' => AttendanceStatus::Hadir,
                'method' => AttendanceMethod::Qr,
                'checked_in_at' => now(),
                'checked_in_by' => null,
                'notes' => null,
            ],
        );

        return response()->view('check-in.result', [
            'success' => true,
            'message' => 'Berhasil check-in untuk kegiatan: '.$event->title,
            'event' => $event,
            'member' => $participant->member,
        ]);
    }
}
