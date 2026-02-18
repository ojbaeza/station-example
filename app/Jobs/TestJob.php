<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class TestJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly int $jobNumber,
        public readonly string $batchId,
        public readonly bool $shouldFail = false,
    ) {}

    public function handle(): void
    {
        $connection = $this->connection ?? config('queue.default');

        // Track worker activity for this connection
        Cache::put("worker:{$connection}:last_activity", now()->toIso8601String(), now()->addMinutes(5));

        Log::info("TestJob #{$this->jobNumber} started", [
            'batch_id' => $this->batchId,
            'queue' => $this->queue ?? 'default',
            'connection' => $connection,
        ]);

        // Simulate work with random sleep (100-500ms)
        usleep(random_int(100000, 500000));

        // Simulate failure if requested (for testing failed job tracking)
        if ($this->shouldFail) {
            Log::error("TestJob #{$this->jobNumber} failed intentionally", [
                'batch_id' => $this->batchId,
            ]);

            throw new RuntimeException("TestJob #{$this->jobNumber} failed intentionally");
        }

        // Increment completed counter
        $key = "batch:{$this->batchId}:completed";
        Cache::increment($key);

        // Update activity timestamp
        Cache::put("worker:{$connection}:last_activity", now()->toIso8601String(), now()->addMinutes(5));

        Log::info("TestJob #{$this->jobNumber} completed", [
            'batch_id' => $this->batchId,
            'total_completed' => Cache::get($key, 0),
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        $key = "batch:{$this->batchId}:failed";
        Cache::increment($key);

        Log::error("TestJob #{$this->jobNumber} failed", [
            'batch_id' => $this->batchId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
