<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Batch;

use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Station\Core\BatchManager;
use Tests\TestCase;

class RetryBatchTest extends TestCase
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

    public function testRetryBatchReturnsSuccessWithRetriedCount(): void
    {
        $this->batchManager->shouldReceive('retryFailed')
            ->once()
            ->with('batch-789')
            ->andReturn(5);

        $response = $this->postJson('/api/station/batch/batch-789/retry');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('retried', 5)
            ->assertJsonPath('message', 'Retried 5 failed jobs');
    }

    public function testRetryBatchWithZeroFailedJobsReturnsZeroRetried(): void
    {
        $this->batchManager->shouldReceive('retryFailed')
            ->once()
            ->with('batch-clean')
            ->andReturn(0);

        $response = $this->postJson('/api/station/batch/batch-clean/retry');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('retried', 0);
    }

    public function testRetryBatchHandlesExceptionReturns500(): void
    {
        $this->batchManager->shouldReceive('retryFailed')
            ->once()
            ->andThrow(new RuntimeException('Queue connection lost'));

        $response = $this->postJson('/api/station/batch/batch-err/retry');

        $response->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error', 'Queue connection lost');
    }
}
