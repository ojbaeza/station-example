<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Batch;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ClearBatchesTest extends TestCase
{
    public function testClearBatchesRemovesAllBatchDataFromCache(): void
    {
        $batchId = 'test-batch-clear';

        Cache::put('active_batches', [
            $batchId => [
                'id' => $batchId,
                'driver' => 'rabbitmq',
                'queue' => 'default',
                'total' => 5,
                'started_at' => now()->toIso8601String(),
            ],
        ], now()->addHour());

        Cache::put("batch:{$batchId}:total", 5, now()->addHour());
        Cache::put("batch:{$batchId}:completed", 3, now()->addHour());
        Cache::put("batch:{$batchId}:failed", 1, now()->addHour());
        Cache::put("batch:{$batchId}:driver", 'rabbitmq', now()->addHour());
        Cache::put("batch:{$batchId}:queue", 'default', now()->addHour());
        Cache::put("batch:{$batchId}:started_at", now()->toIso8601String(), now()->addHour());

        $response = $this->postJson('/batches/clear');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertNull(Cache::get('active_batches'));
        $this->assertNull(Cache::get("batch:{$batchId}:total"));
        $this->assertNull(Cache::get("batch:{$batchId}:completed"));
        $this->assertNull(Cache::get("batch:{$batchId}:failed"));
        $this->assertNull(Cache::get("batch:{$batchId}:driver"));
        $this->assertNull(Cache::get("batch:{$batchId}:queue"));
        $this->assertNull(Cache::get("batch:{$batchId}:started_at"));
    }

    public function testClearBatchesSucceedsWhenNoBatchesExist(): void
    {
        Cache::forget('active_batches');

        $response = $this->postJson('/batches/clear');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function testClearBatchesClearsMultipleBatches(): void
    {
        $batches = [];
        for ($i = 0; $i < 3; $i++) {
            $id = "batch-{$i}";
            $batches[$id] = [
                'id' => $id,
                'driver' => 'redis',
                'queue' => 'default',
                'total' => 1,
                'started_at' => now()->toIso8601String(),
            ];
            Cache::put("batch:{$id}:total", 1, now()->addHour());
        }

        Cache::put('active_batches', $batches, now()->addHour());

        $response = $this->postJson('/batches/clear');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        for ($i = 0; $i < 3; $i++) {
            $this->assertNull(Cache::get("batch:batch-{$i}:total"));
        }
    }
}
