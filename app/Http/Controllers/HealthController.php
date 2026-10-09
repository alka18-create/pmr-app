<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Endpoint uptime monitoring (UptimeRobot/BetterStack) — docs/09 §7.
 * GET /health → 200 bila semua komponen ok, 503 bila ada yang gagal.
 */
class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'db' => $this->check(fn () => DB::select('select 1')),
            'cache' => $this->check(fn () => Redis::ping()),
            'storage' => $this->check(fn () => is_writable(storage_path('framework'))),
        ];

        $ok = ! in_array('failed', $checks, true);

        return response()->json([
            'status' => $ok ? 'ok' : 'failed',
            'checks' => $checks,
        ], $ok ? 200 : 503);
    }

    private function check(callable $probe): string
    {
        try {
            $probe();

            return 'ok';
        } catch (\Throwable) {
            return 'failed';
        }
    }
}
