<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use Illuminate\Support\Facades\Cache;

class ClearBatches
{
    public function handle(): array
    {
        $activeBatches = Cache::get('active_batches', []);

        foreach (array_keys($activeBatches) as $batchId) {
            Cache::forget("batch:{$batchId}:total");
            Cache::forget("batch:{$batchId}:completed");
            Cache::forget("batch:{$batchId}:failed");
            Cache::forget("batch:{$batchId}:driver");
            Cache::forget("batch:{$batchId}:queue");
            Cache::forget("batch:{$batchId}:started_at");
        }

        Cache::forget('active_batches');

        return ['success' => true];
    }
}
