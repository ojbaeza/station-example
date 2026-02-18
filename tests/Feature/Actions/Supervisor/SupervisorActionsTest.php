<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Supervisor;

use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Station\Core\ProcessManager;
use Tests\TestCase;

class SupervisorActionsTest extends TestCase
{
    private MockInterface $processManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->processManager = Mockery::mock(ProcessManager::class);
        $this->app->instance(ProcessManager::class, $this->processManager);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // -- StartSupervisor tests --

    public function testStartSupervisorWithValidDataReturnsSuccess(): void
    {
        $this->processManager->shouldReceive('startSupervisor')
            ->once()
            ->with('rabbitmq', 'default', 3)
            ->andReturn(['success' => true, 'pid' => 1111, 'message' => 'Supervisor started']);

        $response = $this->postJson('/supervisor/start', [
            'connection' => 'rabbitmq',
            'queue' => 'default',
            'workers' => 3,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function testStartSupervisorWithDefaultsWhenOptionalFieldsOmitted(): void
    {
        $this->processManager->shouldReceive('startSupervisor')
            ->once()
            ->andReturn(['success' => true, 'pid' => 2222, 'message' => 'Started']);

        $response = $this->postJson('/supervisor/start', []);

        $response->assertStatus(200);
    }

    public function testStartSupervisorValidationRejectsInvalidConnection(): void
    {
        $response = $this->postJson('/supervisor/start', [
            'connection' => 'invalid_connection',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['connection']);
    }

    public function testStartSupervisorValidationRejectsWorkersAboveMaximum(): void
    {
        $response = $this->postJson('/supervisor/start', [
            'connection' => 'rabbitmq',
            'workers' => 11,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['workers']);
    }

    public function testStartSupervisorValidationRejectsWorkersBelowMinimum(): void
    {
        $response = $this->postJson('/supervisor/start', [
            'connection' => 'rabbitmq',
            'workers' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['workers']);
    }

    public function testStartSupervisorValidationRejectsQueueExceedingMaxLength(): void
    {
        $response = $this->postJson('/supervisor/start', [
            'connection' => 'rabbitmq',
            'queue' => str_repeat('x', 256),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['queue']);
    }

    public function testStartSupervisorHandlesExceptionReturns500(): void
    {
        $this->processManager->shouldReceive('startSupervisor')
            ->andThrow(new RuntimeException('Supervisor already running'));

        $response = $this->postJson('/supervisor/start', []);

        $response->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error', 'Supervisor already running');
    }

    // -- StopSupervisor tests --

    public function testStopSupervisorReturnsSuccess(): void
    {
        $this->processManager->shouldReceive('stopSupervisor')
            ->once()
            ->andReturn(['success' => true, 'message' => 'Supervisor stopped']);

        $response = $this->postJson('/supervisor/stop');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function testStopSupervisorHandlesExceptionReturns500(): void
    {
        $this->processManager->shouldReceive('stopSupervisor')
            ->andThrow(new RuntimeException('No supervisor running'));

        $response = $this->postJson('/supervisor/stop');

        $response->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error', 'No supervisor running');
    }

    // -- GetSupervisorStatus tests --

    public function testGetSupervisorStatusReturnsJsonResponse(): void
    {
        $this->processManager->shouldReceive('getSupervisorStatus')
            ->once()
            ->andReturn([
                'running' => true,
                'pid' => 3333,
                'workers' => 4,
                'uptime' => '2h 15m',
            ]);

        $response = $this->getJson('/supervisor/status');

        $response->assertStatus(200)
            ->assertJsonPath('running', true)
            ->assertJsonPath('pid', 3333);
    }

    public function testGetSupervisorStatusHandlesExceptionGracefully(): void
    {
        $this->processManager->shouldReceive('getSupervisorStatus')
            ->andThrow(new RuntimeException('Process management disabled'));

        $response = $this->getJson('/supervisor/status');

        $response->assertStatus(200)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error', 'Process management disabled');
    }
}
