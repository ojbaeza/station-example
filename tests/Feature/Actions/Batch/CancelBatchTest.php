<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Batch;

use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Station\Core\BatchManager;
use Tests\TestCase;

class CancelBatchTest extends TestCase
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

    public function testCancelBatchWithValidIdReturnsSuccess(): void
    {
        $this->batchManager->shouldReceive('cancel')
            ->once()
            ->with('batch-123')
            ->andReturn(true);

        $response = $this->postJson('/api/station/batch/batch-123/cancel');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Batch cancelled');
    }

    public function testCancelBatchWhenCancelFailsReturnsFailureMessage(): void
    {
        $this->batchManager->shouldReceive('cancel')
            ->once()
            ->with('batch-456')
            ->andReturn(false);

        $response = $this->postJson('/api/station/batch/batch-456/cancel');

        $response->assertStatus(200)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Unable to cancel batch');
    }

    public function testCancelBatchHandlesBatchManagerException(): void
    {
        $this->batchManager->shouldReceive('cancel')
            ->once()
            ->andThrow(new RuntimeException('Batch not found'));

        $response = $this->postJson('/api/station/batch/nonexistent/cancel');

        $response->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error', 'Batch not found');
    }
}
