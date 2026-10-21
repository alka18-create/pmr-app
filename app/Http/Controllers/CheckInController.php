<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceMethod;
use App\Enums\AttendanceStatus;
use App\Enums\EventStatus;
use App\Models\AttendanceRecord;
use App\Models\EventParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Check-in absensi via QR.
 *
 * GET  = halaman konfirmasi (read-only, tidak mengubah data) agar prefetch /
 *        pemindai tautan tidak mencatat absensi tanpa disengaja.
 * POST = mencatat kehadiran (dilindungi CSRF).
 *
 * Token = UUID v4 acak (122 bit) sehingga tidak dapat ditebak.
 */
class CheckInController extends Controller
{
    /** Batas per menit per IP — longgar karena satu kelas bisa berbagi IP Wi-Fi sekolah. */
    public const MAX_ATTEMPTS_PER_MINUTE = 30;

    public function show(Request $request, string $token): Response
    {
        if ($blocked = $this->throttle($request)) {
            return $blocked;
        }

        $participant = $this->resolveParticipant($token);

        if ($participant instanceof Response) {
            return $participant;
        }

        return response()->view('check-in.confirm', [
            'token' => $token,
            'event' => $participant->event,
            'member' => $participant->member,
        ]);
    }

    public function store(Request $request, string $token): Response
    {
        if ($blocked = $this->throttle($request)) {
            return $blocked;
        }

        $participant = $this->resolveParticipant($token);

        if ($participant instanceof Response) {
            return $participant;
        }

        $event = $participant->event;

        $existing = AttendanceRecord::where('event_id', $event->id)
            ->where('member_id', $participant->member_id)
            ->first();

        if ($existing && $existing->status === AttendanceStatus::Hadir) {
            return $this->failure('Anda sudah check-in untuk kegiatan ini.', 409);
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

    private function throttle(Request $request): ?Response
    {
        $limiterKey = 'check-in:'.($request->ip() ?? 'unknown');

        if (RateLimiter::tooManyAttempts($limiterKey, self::MAX_ATTEMPTS_PER_MINUTE)) {
            $seconds = RateLimiter::availableIn($limiterKey);

            return $this->failure('Terlalu banyak percobaan. Coba lagi dalam '.$seconds.' detik.', 429)
                ->header('Retry-After', (string) $seconds);
        }

        RateLimiter::hit($limiterKey, 60);

        return null;
    }

    private function resolveParticipant(string $token): EventParticipant|Response
    {
        $participant = EventParticipant::with(['event', 'member:id,full_name,slug'])
            ->where('qr_token', $token)
            ->first();

        if (! $participant) {
            return $this->failure('Token tidak ditemukan.', 404);
        }

        $event = $participant->event;

        if (! $event || in_array($event->status, [EventStatus::Draft, EventStatus::Batal], true)) {
            return $this->failure('Kegiatan tidak aktif.', 422);
        }

        if ($event->ends_at && $event->ends_at->isPast()) {
            return $this->failure('Kegiatan sudah berakhir.', 422);
        }

        return $participant;
    }

    private function failure(string $message, int $status): \Illuminate\Http\Response
    {
        return response()->view('check-in.result', [
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
