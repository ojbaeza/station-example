<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Batch;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GetBatchStatusTest extends TestCase
{
    public function testGetBatchStatusReturnsCorrectDataForExistingBatch(): void
    {
        $batchId = 'test-batch-123';

        Cache::put("batch:{$batchId}:total", 10, now()->addHour());
        Cache::put("batch:{$batchId}:completed", 7, now()->addHour());
        Cache::put("batch:{$batchId}:failed", 1, now()->addHour());
        Cache::put("batch:{$batchId}:driver", 'rabbitmq', now()->addHour());
        Cache::put("batch:{$batchId}:queue", 'high', now()->addHour());
        Cache::put("batch:{$batchId}:started_at", '2025-01-01T00:00:00+00:00', now()->addHour());

        $response = $this->getJson("/status/{$batchId}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'batch_id',
                'driver',
                'queue',
                'total',
                'completed',
                'failed',
                'pending',
                'progress',
                'started_at',
                'is_complete',
            ])
            ->assertJsonPath('batch_id', $batchId)
            ->assertJsonPath('driver', 'rabbitmq')
            ->assertJsonPath('queue', 'high')
            ->assertJsonPath('total', 10)
            ->assertJsonPath('completed', 7)
            ->assertJsonPath('failed', 1)
            ->assertJsonPath('pending', 2)
            ->assertJsonPath('progress', 80)
            ->assertJsonPath('is_complete', false);
    }

    public function testGetBatchStatusReturnsIsCompleteWhenAllJobsFinished(): void
    {
        $batchId = 'completed-batch';

        Cache::put("batch:{$batchId}:total", 5, now()->addHour());
        Cache::put("batch:{$batchId}:completed", 4, now()->addHour());
        Cache::put("batch:{$batchId}:failed", 1, now()->addHour());

        $response = $this->getJson("/status/{$batchId}");

        $response->assertStatus(200)
            ->assertJsonPath('is_complete', true)
            ->assertJsonPath('progress', 100)
            ->assertJsonPath('pending', 0);
    }

    public function testGetBatchStatusReturnsDefaultsForNonExistentBatch(): void
    {
        $response = $this->getJson('/status/nonexistent-batch');

        $response->assertStatus(200)
            ->assertJsonPath('total', 0)
            ->assertJsonPath('completed', 0)
            ->assertJsonPath('failed', 0)
            ->assertJsonPath('pending', 0)
            ->assertJsonPath('progress', 0)
            ->assertJsonPath('driver', 'unknown')
            ->assertJsonPath('is_complete', false);
    }

    public function testGetBatchStatusCalculatesProgressCorrectly(): void
    {
        $batchId = 'progress-batch';

        Cache::put("batch:{$batchId}:total", 100, now()->addHour());
        Cache::put("batch:{$batchId}:completed", 33, now()->addHour());
        Cache::put("batch:{$batchId}:failed", 7, now()->addHour());

        $response = $this->getJson("/status/{$batchId}");

        $response->assertStatus(200)
            ->assertJsonPath('progress', 40)
            ->assertJsonPath('pending', 60);
    }

    public function testGetBatchStatusPendingNeverGoesNegative(): void
    {
        $batchId = 'overflow-batch';

        // Simulate race condition where completed + failed > total
        Cache::put("batch:{$batchId}:total", 5, now()->addHour());
        Cache::put("batch:{$batchId}:completed", 4, now()->addHour());
        Cache::put("batch:{$batchId}:failed", 3, now()->addHour());

        $response = $this->getJson("/status/{$batchId}");

        $response->assertStatus(200)
            ->assertJsonPath('pending', 0);
    }

    public function testGetBatchStatusReturnsStartedAtTimestamp(): void
    {
        $batchId = 'timestamp-batch';
        $startedAt = '2025-06-15T10:30:00+00:00';

        Cache::put("batch:{$batchId}:total", 1, now()->addHour());
        Cache::put("batch:{$batchId}:completed", 0, now()->addHour());
        Cache::put("batch:{$batchId}:failed", 0, now()->addHour());
        Cache::put("batch:{$batchId}:started_at", $startedAt, now()->addHour());

        $response = $this->getJson("/status/{$batchId}");

        $response->assertStatus(200)
            ->assertJsonPath('started_at', $startedAt);
    }

    public function testGetBatchStatusWithOnlyFailedJobsReturnsComplete(): void
    {
        $batchId = 'all-failed-batch';

        Cache::put("batch:{$batchId}:total", 3, now()->addHour());
        Cache::put("batch:{$batchId}:completed", 0, now()->addHour());
        Cache::put("batch:{$batchId}:failed", 3, now()->addHour());

        $response = $this->getJson("/status/{$batchId}");

        $response->assertStatus(200)
            ->assertJsonPath('is_complete', true)
            ->assertJsonPath('progress', 100)
            ->assertJsonPath('completed', 0)
            ->assertJsonPath('failed', 3);
    }
}
