<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class HealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_publik_tanpa_login(): void
    {
        Redis::shouldReceive('ping')->andReturn(true);

        $this->get(route('health'))
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
                'checks' => ['db' => 'ok', 'cache' => 'ok', 'storage' => 'ok'],
            ]);
    }

    public function test_health_503_bila_redis_gagal(): void
    {
        Redis::shouldReceive('ping')->andThrow(new \RuntimeException('redis down'));

        $this->get(route('health'))
            ->assertServiceUnavailable()
            ->assertJson([
                'status' => 'failed',
                'checks' => ['cache' => 'failed'],
            ]);
    }
}
