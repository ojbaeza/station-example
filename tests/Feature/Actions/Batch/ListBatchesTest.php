<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Batch;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ListBatchesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function testListBatchesReturnsEmptyListWhenNoBatches(): void
    {
        $response = $this->getJson('/batches');

        $response->assertStatus(200)
            ->assertJsonPath('batches', []);
    }

    public function testListBatchesReturnsBatchDetails(): void
    {
        $batchId = 'batch-001';
        $startedAt = now()->toIso8601String();

        Cache::put('active_batches', [
            $batchId => [
                'id' => $batchId,
                'driver' => 'rabbitmq',
                'queue' => 'default',
                'total' => 10,
                'started_at' => $startedAt,
            ],
        ], now()->addHour());

        Cache::put("batch:{$batchId}:total", 10, now()->addHour());
        Cache::put("batch:{$batchId}:completed", 5, now()->addHour());
        Cache::put("batch:{$batchId}:failed", 1, now()->addHour());

        $response = $this->getJson('/batches');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'batches')
            ->assertJsonPath('batches.0.id', $batchId)
            ->assertJsonPath('batches.0.driver', 'rabbitmq')
            ->assertJsonPath('batches.0.total', 10)
            ->assertJsonPath('batches.0.completed', 5)
            ->assertJsonPath('batches.0.failed', 1)
            ->assertJsonPath('batches.0.pending', 4);
    }

    public function testListBatchesFiltersByConnection(): void
    {
        $startedAt = now()->toIso8601String();

        Cache::put('active_batches', [
            'batch-rmq' => [
                'id' => 'batch-rmq',
                'driver' => 'rabbitmq',
                'queue' => 'default',
                'total' => 5,
                'started_at' => $startedAt,
            ],
            'batch-redis' => [
                'id' => 'batch-redis',
                'driver' => 'redis',
                'queue' => 'default',
                'total' => 3,
                'started_at' => $startedAt,
            ],
        ], now()->addHour());

        Cache::put('batch:batch-rmq:total', 5, now()->addHour());
        Cache::put('batch:batch-rmq:completed', 0, now()->addHour());
        Cache::put('batch:batch-rmq:failed', 0, now()->addHour());
        Cache::put('batch:batch-redis:total', 3, now()->addHour());
        Cache::put('batch:batch-redis:completed', 0, now()->addHour());
        Cache::put('batch:batch-redis:failed', 0, now()->addHour());

        $response = $this->getJson('/batches?connection=rabbitmq');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'batches')
            ->assertJsonPath('batches.0.id', 'batch-rmq');
    }

    public function testListBatchesFiltersByStatusPending(): void
    {
        $startedAt = now()->toIso8601String();

        Cache::put('active_batches', [
            'batch-pending' => [
                'id' => 'batch-pending',
                'driver' => 'rabbitmq',
                'queue' => 'default',
                'total' => 5,
                'started_at' => $startedAt,
            ],
            'batch-complete' => [
                'id' => 'batch-complete',
                'driver' => 'rabbitmq',
                'queue' => 'default',
                'total' => 5,
                'started_at' => $startedAt,
            ],
        ], now()->addHour());

        // Pending batch
        Cache::put('batch:batch-pending:total', 5, now()->addHour());
        Cache::put('batch:batch-pending:completed', 2, now()->addHour());
        Cache::put('batch:batch-pending:failed', 0, now()->addHour());

        // Complete batch
        Cache::put('batch:batch-complete:total', 5, now()->addHour());
        Cache::put('batch:batch-complete:completed', 5, now()->addHour());
        Cache::put('batch:batch-complete:failed', 0, now()->addHour());

        $response = $this->getJson('/batches?status=pending');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'batches')
            ->assertJsonPath('batches.0.id', 'batch-pending');
    }

    public function testListBatchesFiltersByStatusComplete(): void
    {
        $startedAt = now()->toIso8601String();

        Cache::put('active_batches', [
            'batch-complete' => [
                'id' => 'batch-complete',
                'driver' => 'redis',
                'queue' => 'default',
                'total' => 3,
                'started_at' => $startedAt,
            ],
        ], now()->addHour());

        Cache::put('batch:batch-complete:total', 3, now()->addHour());
        Cache::put('batch:batch-complete:completed', 3, now()->addHour());
        Cache::put('batch:batch-complete:failed', 0, now()->addHour());

        $response = $this->getJson('/batches?status=complete');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'batches');
    }

    public function testListBatchesLimitsResultsToTwenty(): void
    {
        $batches = [];
        for ($i = 0; $i < 25; $i++) {
            $id = "batch-{$i}";
            $batches[$id] = [
                'id' => $id,
                'driver' => 'rabbitmq',
                'queue' => 'default',
                'total' => 1,
                'started_at' => now()->subMinutes($i)->toIso8601String(),
            ];
            Cache::put("batch:{$id}:total", 1, now()->addHour());
            Cache::put("batch:{$id}:completed", 0, now()->addHour());
            Cache::put("batch:{$id}:failed", 0, now()->addHour());
        }

        Cache::put('active_batches', $batches, now()->addHour());

        $response = $this->getJson('/batches');

        $response->assertStatus(200)
            ->assertJsonCount(20, 'batches');
    }

    public function testListBatchesFiltersByStatusFailed(): void
    {
        $startedAt = now()->toIso8601String();

        Cache::put('active_batches', [
            'batch-ok' => [
                'id' => 'batch-ok',
                'driver' => 'rabbitmq',
                'queue' => 'default',
                'total' => 5,
                'started_at' => $startedAt,
            ],
            'batch-failed' => [
                'id' => 'batch-failed',
                'driver' => 'rabbitmq',
                'queue' => 'default',
                'total' => 5,
                'started_at' => $startedAt,
            ],
        ], now()->addHour());

        // Successful batch (no failures)
        Cache::put('batch:batch-ok:total', 5, now()->addHour());
        Cache::put('batch:batch-ok:completed', 5, now()->addHour());
        Cache::put('batch:batch-ok:failed', 0, now()->addHour());

        // Failed batch
        Cache::put('batch:batch-failed:total', 5, now()->addHour());
        Cache::put('batch:batch-failed:completed', 3, now()->addHour());
        Cache::put('batch:batch-failed:failed', 2, now()->addHour());

        $response = $this->getJson('/batches?status=failed');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'batches')
            ->assertJsonPath('batches.0.id', 'batch-failed');
    }

    public function testListBatchesFiltersByBothConnectionAndStatus(): void
    {
        $startedAt = now()->toIso8601String();

        Cache::put('active_batches', [
            'batch-rmq-pending' => [
                'id' => 'batch-rmq-pending',
                'driver' => 'rabbitmq',
                'queue' => 'default',
                'total' => 5,
                'started_at' => $startedAt,
            ],
            'batch-redis-pending' => [
                'id' => 'batch-redis-pending',
                'driver' => 'redis',
                'queue' => 'default',
                'total' => 5,
                'started_at' => $startedAt,
            ],
        ], now()->addHour());

        Cache::put('batch:batch-rmq-pending:total', 5, now()->addHour());
        Cache::put('batch:batch-rmq-pending:completed', 2, now()->addHour());
        Cache::put('batch:batch-rmq-pending:failed', 0, now()->addHour());

        Cache::put('batch:batch-redis-pending:total', 5, now()->addHour());
        Cache::put('batch:batch-redis-pending:completed', 1, now()->addHour());
        Cache::put('batch:batch-redis-pending:failed', 0, now()->addHour());

        $response = $this->getJson('/batches?connection=rabbitmq&status=pending');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'batches')
            ->assertJsonPath('batches.0.id', 'batch-rmq-pending');
    }

    public function testListBatchesWithUnknownStatusFilterReturnsAllBatches(): void
    {
        $startedAt = now()->toIso8601String();

        Cache::put('active_batches', [
            'batch-x' => [
                'id' => 'batch-x',
                'driver' => 'rabbitmq',
                'queue' => 'default',
                'total' => 1,
                'started_at' => $startedAt,
            ],
        ], now()->addHour());

        Cache::put('batch:batch-x:total', 1, now()->addHour());
        Cache::put('batch:batch-x:completed', 0, now()->addHour());
        Cache::put('batch:batch-x:failed', 0, now()->addHour());

        // An unknown status value should match the `default => true` branch
        $response = $this->getJson('/batches?status=unknown_value');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'batches');
    }

    public function testListBatchesSortsByStartedAtDescending(): void
    {
        Cache::put('active_batches', [
            'batch-old' => [
                'id' => 'batch-old',
                'driver' => 'redis',
                'queue' => 'default',
                'total' => 1,
                'started_at' => '2025-01-01T00:00:00+00:00',
            ],
            'batch-new' => [
                'id' => 'batch-new',
                'driver' => 'redis',
                'queue' => 'default',
                'total' => 1,
                'started_at' => '2025-06-01T00:00:00+00:00',
            ],
        ], now()->addHour());

        Cache::put('batch:batch-old:total', 1, now()->addHour());
        Cache::put('batch:batch-old:completed', 0, now()->addHour());
        Cache::put('batch:batch-old:failed', 0, now()->addHour());
        Cache::put('batch:batch-new:total', 1, now()->addHour());
        Cache::put('batch:batch-new:completed', 0, now()->addHour());
        Cache::put('batch:batch-new:failed', 0, now()->addHour());

        $response = $this->getJson('/batches');

        $response->assertStatus(200)
            ->assertJsonPath('batches.0.id', 'batch-new')
            ->assertJsonPath('batches.1.id', 'batch-old');
    }
}
