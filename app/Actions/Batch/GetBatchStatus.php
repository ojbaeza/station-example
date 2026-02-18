<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use Illuminate\Support\Facades\Cache;

class GetBatchStatus
{
    public function handle(string $batchId): array
    {
        $total = Cache::get("batch:{$batchId}:total", 0);
        $completed = Cache::get("batch:{$batchId}:completed", 0);
        $failed = Cache::get("batch:{$batchId}:failed", 0);
        $driver = Cache::get("batch:{$batchId}:driver", 'unknown');
        $queue = Cache::get("batch:{$batchId}:queue", 'default');
        $startedAt = Cache::get("batch:{$batchId}:started_at");

        $pending = $total - $completed - $failed;
        $progress = $total > 0 ? round(($completed + $failed) / $total * 100, 2) : 0;

        return [
            'batch_id' => $batchId,
            'driver' => $driver,
            'queue' => $queue,
            'total' => $total,
            'completed' => $completed,
            'failed' => $failed,
            'pending' => max(0, $pending),
            'progress' => $progress,
            'started_at' => $startedAt,
            'is_complete' => $pending <= 0 && $total > 0,
        ];
    }
}
