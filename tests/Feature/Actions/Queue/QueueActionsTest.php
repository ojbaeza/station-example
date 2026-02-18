<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Queue;

use Mockery;
use Mockery\MockInterface;
use Station\Core\QueueManager;
use Tests\TestCase;

class QueueActionsTest extends TestCase
{
    private MockInterface $queueManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->queueManager = Mockery::mock(QueueManager::class);
        $this->app->instance(QueueManager::class, $this->queueManager);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // -- PauseQueue tests --

    public function testPauseQueueWithValidDriverReturnsSuccess(): void
    {
        $this->queueManager->shouldReceive('pause')
            ->once()
            ->with('default', 'rabbitmq');

        $response = $this->postJson('/queue/pause', [
            'driver' => 'rabbitmq',
            'queue' => 'default',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('driver', 'rabbitmq')
            ->assertJsonPath('queue', 'default')
            ->assertJsonPath('paused', true);
    }

    public function testPauseQueueWithDefaultQueueWhenQueueOmitted(): void
    {
        $this->queueManager->shouldReceive('pause')
            ->once()
            ->with('default', 'redis');

        $response = $this->postJson('/queue/pause', [
            'driver' => 'redis',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('queue', 'default');
    }

    public function testPauseQueueValidationRejectsMissingDriver(): void
    {
        $response = $this->postJson('/queue/pause', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['driver']);
    }

    public function testPauseQueueValidationRejectsInvalidDriver(): void
    {
        $response = $this->postJson('/queue/pause', [
            'driver' => 'nonexistent',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['driver']);
    }

    // -- ResumeQueue tests --

    public function testResumeQueueWithValidDriverReturnsSuccess(): void
    {
        $this->queueManager->shouldReceive('resume')
            ->once()
            ->with('high', 'rabbitmq');

        $response = $this->postJson('/queue/resume', [
            'driver' => 'rabbitmq',
            'queue' => 'high',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('driver', 'rabbitmq')
            ->assertJsonPath('queue', 'high')
            ->assertJsonPath('paused', false);
    }

    public function testResumeQueueValidationRejectsMissingDriver(): void
    {
        $response = $this->postJson('/queue/resume', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['driver']);
    }

    public function testResumeQueueWithDefaultQueueWhenQueueOmitted(): void
    {
        $this->queueManager->shouldReceive('resume')
            ->once()
            ->with('default', 'sqs');

        $response = $this->postJson('/queue/resume', [
            'driver' => 'sqs',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('queue', 'default')
            ->assertJsonPath('paused', false);
    }

    public function testResumeQueueValidationRejectsInvalidDriver(): void
    {
        $response = $this->postJson('/queue/resume', [
            'driver' => 'invalid_driver',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['driver']);
    }

    public function testPauseQueueValidationRejectsQueueExceedingMaxLength(): void
    {
        $response = $this->postJson('/queue/pause', [
            'driver' => 'rabbitmq',
            'queue' => str_repeat('x', 256),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['queue']);
    }

    // -- GetQueuePauseStatus tests --

    public function testGetQueuePauseStatusReturnsStatusForAllDrivers(): void
    {
        $this->queueManager->shouldReceive('isPaused')
            ->andReturnUsing(static fn(string $queue, string $driver): bool => $driver === 'rabbitmq');

        $response = $this->getJson('/queue/pause-status');

        $response->assertStatus(200)
            ->assertJsonStructure(['paused'])
            ->assertJsonPath('paused.rabbitmq', true)
            ->assertJsonPath('paused.redis', false);
    }
}
