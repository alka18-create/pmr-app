<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Throttle global route publik — docs/08 §2.6 (60 request/menit/IP).
 */
class PublicThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_route_publik_dibatasi_60_request_per_menit_per_ip(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $this->get('/robots.txt')->assertOk();
        }

        $this->get('/robots.txt')->assertStatus(429);
        $this->get('/berita')->assertStatus(429);
    }

    public function test_batas_dihitung_per_ip(): void
    {
        for ($i = 0; $i < 61; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])->get('/robots.txt');
        }

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])->get('/robots.txt')->assertStatus(429);
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])->get('/robots.txt')->assertOk();
    }

    public function test_health_check_tidak_terkena_throttle_publik(): void
    {
        for ($i = 0; $i < 70; $i++) {
            $this->get('/robots.txt');
        }

        $this->get('/robots.txt')->assertStatus(429);
        $this->get('/health')->assertStatus(200);
    }
}
