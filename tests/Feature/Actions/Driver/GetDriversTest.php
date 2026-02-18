<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Driver;

use RuntimeException;
use Station\Contracts\HealthCheckerInterface;
use Station\DTOs\ConnectionStatus;
use Tests\TestCase;

class GetDriversTest extends TestCase
{
    public function testGetDriversReturnsJsonWithDriverList(): void
    {
        $healthChecker = $this->createMock(HealthCheckerInterface::class);
        $healthChecker->expects($this->once())
            ->method('checkConnectivityQuick')
            ->willReturn([
                'rabbitmq' => new ConnectionStatus(
                    connected: true,
                    latency_ms: 5,
                    driver: 'rabbitmq',
                ),
                'redis' => new ConnectionStatus(
                    connected: false,
                    latency_ms: 0,
                    driver: 'redis',
                    message: 'Connection refused',
                ),
            ]);

        $this->app->instance(HealthCheckerInterface::class, $healthChecker);

        $response = $this->getJson('/drivers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'drivers' => [
                    'rabbitmq' => ['connected', 'latency_ms', 'status'],
                    'redis' => ['connected', 'latency_ms', 'status'],
                ],
                'default',
            ])
            ->assertJsonPath('drivers.rabbitmq.status', 'connected')
            ->assertJsonPath('drivers.redis.status', 'disconnected');
    }

    public function testGetDriversIncludesDefaultConnection(): void
    {
        $healthChecker = $this->createMock(HealthCheckerInterface::class);
        $healthChecker->method('checkConnectivityQuick')->willReturn([]);

        $this->app->instance(HealthCheckerInterface::class, $healthChecker);

        $response = $this->getJson('/drivers');

        $response->assertStatus(200)
            ->assertJsonStructure(['default']);
    }

    public function testGetDriversHandlesHealthCheckerException(): void
    {
        $healthChecker = $this->createMock(HealthCheckerInterface::class);
        $healthChecker->expects($this->once())
            ->method('checkConnectivityQuick')
            ->willThrowException(new RuntimeException('Health check service unavailable'));

        $this->app->instance(HealthCheckerInterface::class, $healthChecker);

        $response = $this->getJson('/drivers');

        $response->assertStatus(200)
            ->assertJsonPath('drivers', [])
            ->assertJsonPath('error', 'Health check service unavailable');
    }

    public function testGetDriversWithEmptyConnectivityReturnsEmptyDrivers(): void
    {
        $healthChecker = $this->createMock(HealthCheckerInterface::class);
        $healthChecker->method('checkConnectivityQuick')->willReturn([]);

        $this->app->instance(HealthCheckerInterface::class, $healthChecker);

        $response = $this->getJson('/drivers');

        $response->assertStatus(200)
            ->assertJsonPath('drivers', []);
    }

    public function testGetDriversReturnsConnectedStatusString(): void
    {
        $healthChecker = $this->createMock(HealthCheckerInterface::class);
        $healthChecker->method('checkConnectivityQuick')->willReturn([
            'rabbitmq' => new ConnectionStatus(
                connected: true,
                latency_ms: 3,
                driver: 'rabbitmq',
            ),
        ]);

        $this->app->instance(HealthCheckerInterface::class, $healthChecker);

        $response = $this->getJson('/drivers');

        $response->assertStatus(200)
            ->assertJsonPath('drivers.rabbitmq.connected', true)
            ->assertJsonPath('drivers.rabbitmq.status', 'connected')
            ->assertJsonPath('drivers.rabbitmq.latency_ms', 3);
    }

    public function testGetDriversReturnsDisconnectedStatusWithMessage(): void
    {
        $healthChecker = $this->createMock(HealthCheckerInterface::class);
        $healthChecker->method('checkConnectivityQuick')->willReturn([
            'sqs' => new ConnectionStatus(
                connected: false,
                latency_ms: 0,
                driver: 'sqs',
                message: 'Invalid credentials',
            ),
        ]);

        $this->app->instance(HealthCheckerInterface::class, $healthChecker);

        $response = $this->getJson('/drivers');

        $response->assertStatus(200)
            ->assertJsonPath('drivers.sqs.connected', false)
            ->assertJsonPath('drivers.sqs.status', 'disconnected');
    }

    public function testGetDriversErrorResponseStillIncludesDefault(): void
    {
        $healthChecker = $this->createMock(HealthCheckerInterface::class);
        $healthChecker->method('checkConnectivityQuick')
            ->willThrowException(new RuntimeException('Service down'));

        $this->app->instance(HealthCheckerInterface::class, $healthChecker);

        $response = $this->getJson('/drivers');

        $response->assertStatus(200)
            ->assertJsonStructure(['default', 'error'])
            ->assertJsonPath('drivers', []);
    }
}
