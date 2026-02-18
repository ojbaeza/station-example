<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use App\Jobs\TestJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Station\Core\JobManager;

class DispatchJobs
{
    public function __construct(
        private readonly JobManager $jobManager,
    ) {}

    public function handle(string $driver, int $count, string $queue, int $delay, int $failPercentage): array
    {
        $batchId = Str::uuid()->toString();

        Cache::put("batch:{$batchId}:total", $count, now()->addHours(24));
        Cache::put("batch:{$batchId}:completed", 0, now()->addHours(24));
        Cache::put("batch:{$batchId}:failed", 0, now()->addHours(24));
        Cache::put("batch:{$batchId}:driver", $driver, now()->addHours(24));
        Cache::put("batch:{$batchId}:queue", $queue, now()->addHours(24));
        Cache::put("batch:{$batchId}:started_at", now()->toIso8601String(), now()->addHours(24));

        $activeBatches = Cache::get('active_batches', []);
        $activeBatches[$batchId] = [
            'id' => $batchId,
            'driver' => $driver,
            'queue' => $queue,
            'total' => $count,
            'started_at' => now()->toIso8601String(),
        ];
        Cache::put('active_batches', $activeBatches, now()->addHours(24));

        $chunkSize = 1000;
        for ($i = 0; $i < $count; $i += $chunkSize) {
            $end = min($i + $chunkSize, $count);
            for ($j = $i; $j < $end; $j++) {
                $shouldFail = $failPercentage > 0 && random_int(1, 100) <= $failPercentage;

                $job = new TestJob($j + 1, $batchId, $shouldFail);

                $pendingDispatch = $this->jobManager->job($job)
                    ->onConnection($driver)
                    ->onQueue($queue);
                if ($delay > 0) {
                    $pendingDispatch->delay(now()->addSeconds($delay));
                }
                $pendingDispatch->dispatch();
            }
        }

        return [
            'batch_id' => $batchId,
            'total' => $count,
            'driver' => $driver,
            'queue' => $queue,
            'delay' => $delay,
        ];
    }
}
