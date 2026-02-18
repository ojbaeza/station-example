<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\TestJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\TestCase;

class TestJobHandleTest extends TestCase
{
    public function testHandleIncrementsBatchCompletedCounter(): void
    {
        $batchId = 'test-batch-123';
        $job = new TestJob(jobNumber: 1, batchId: $batchId);

        Log::shouldReceive('info')->twice();

        $job->handle();

        $this->assertGreaterThanOrEqual(1, Cache::get("batch:{$batchId}:completed", 0));
    }

    public function testHandleThrowsWhenShouldFailIsTrue(): void
    {
        $job = new TestJob(jobNumber: 1, batchId: 'fail-batch', shouldFail: true);

        Log::shouldReceive('info')->once();
        Log::shouldReceive('error')->once();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('TestJob #1 failed intentionally');

        $job->handle();
    }

    public function testFailedMethodIncrementsBatchFailedCounter(): void
    {
        $batchId = 'fail-batch-456';
        $job = new TestJob(jobNumber: 5, batchId: $batchId);

        Log::shouldReceive('error')->once();

        $job->failed(new RuntimeException('Something went wrong'));

        $this->assertEquals(1, Cache::get("batch:{$batchId}:failed", 0));
    }
}
