<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\BatchTestJob;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class BatchTestJobHandleTest extends TestCase
{
    public function testHandleReturnsEarlyWhenBatchIsCancelled(): void
    {
        $job = new BatchTestJob(jobNumber: 1, name: 'cancel-test');

        // withFakeBatch returns [$job, $fakeBatch] — set cancelledAt to simulate cancellation
        [$job, $batch] = $job->withFakeBatch(cancelledAt: CarbonImmutable::now());

        Log::shouldReceive('info')->never();

        $job->handle();

        // If we got here without logging, the early return worked
        $this->assertTrue(true);
    }

    public function testHandleCompletesNormallyWhenNotCancelled(): void
    {
        $job = new BatchTestJob(jobNumber: 1, name: 'normal-test', processingTimeMs: 10);

        Log::shouldReceive('info')->twice();

        // Without a batch set, batch() returns null, so the cancelled check is skipped
        $job->handle();

        $this->assertTrue(true);
    }
}
