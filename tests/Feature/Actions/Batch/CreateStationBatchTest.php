<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Batch;

use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Station\Core\Batch;
use Station\Core\BatchManager;
use Tests\TestCase;

class CreateStationBatchTest extends TestCase
{
    private MockInterface $batchManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->batchManager = Mockery::mock(BatchManager::class);
        $this->app->instance(BatchManager::class, $this->batchManager);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testCreateStationBatchWithValidDataReturnsSuccess(): void
    {
        $batch = new Batch(
            id: 'batch-uuid-123',
            name: 'Test Batch',
            queue: 'default',
            totalJobs: 5,
        );

        $this->batchManager->shouldReceive('create')
            ->once()
            ->andReturn($batch);

        $response = $this->postJson('/api/station/batch', [
            'name' => 'Test Batch',
            'count' => 5,
            'queue' => 'default',
            'connection' => 'rabbitmq',
            'allowed_failures' => 2,
            'fail_percentage' => 10,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'batch_id',
                'name',
                'total_jobs',
                'queue',
                'connection',
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('batch_id', 'batch-uuid-123')
            ->assertJsonPath('total_jobs', 5);
    }

    public function testCreateStationBatchWithMinimalDataReturnsSuccess(): void
    {
        $batch = new Batch(
            id: 'batch-minimal',
            totalJobs: 1,
        );

        $this->batchManager->shouldReceive('create')
            ->once()
            ->andReturn($batch);

        $response = $this->postJson('/api/station/batch', [
            'count' => 1,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function testCreateStationBatchValidationRejectsMissingCount(): void
    {
        $response = $this->postJson('/api/station/batch', [
            'name' => 'Test',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['count']);
    }

    public function testCreateStationBatchValidationRejectsCountBelowMinimum(): void
    {
        $response = $this->postJson('/api/station/batch', [
            'count' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['count']);
    }

    public function testCreateStationBatchValidationRejectsCountAboveMaximum(): void
    {
        $response = $this->postJson('/api/station/batch', [
            'count' => 1001,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['count']);
    }

    public function testCreateStationBatchValidationRejectsLongName(): void
    {
        $response = $this->postJson('/api/station/batch', [
            'name' => str_repeat('a', 256),
            'count' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function testCreateStationBatchHandlesBatchManagerException(): void
    {
        $this->batchManager->shouldReceive('create')
            ->once()
            ->andThrow(new RuntimeException('Database connection failed'));

        $response = $this->postJson('/api/station/batch', [
            'count' => 5,
        ]);

        $response->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error', 'Database connection failed');
    }

    public function testCreateStationBatchValidationRejectsNegativeAllowedFailures(): void
    {
        $response = $this->postJson('/api/station/batch', [
            'count' => 1,
            'allowed_failures' => -1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['allowed_failures']);
    }
}
