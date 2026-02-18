<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Test job for Station batches.
 */
class BatchTestJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly int $jobNumber,
        public readonly string $name,
        public readonly bool $shouldFail = false,
        public readonly int $processingTimeMs = 200,
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $connection = $this->connection ?? config('queue.default');

        // Track worker activity for this connection
        Cache::put("worker:{$connection}:last_activity", now()->toIso8601String(), now()->addMinutes(5));

        Log::info("BatchTestJob #{$this->jobNumber} ({$this->name}) started");

        // Simulate work
        usleep($this->processingTimeMs * 1000);

        if ($this->shouldFail) {
            throw new RuntimeException("BatchTestJob #{$this->jobNumber} failed intentionally");
        }

        // Update activity timestamp
        Cache::put("worker:{$connection}:last_activity", now()->toIso8601String(), now()->addMinutes(5));

        Log::info("BatchTestJob #{$this->jobNumber} ({$this->name}) completed");
    }

    public function tags(): array
    {
        return ['batch-test', "job-{$this->jobNumber}"];
    }
}
