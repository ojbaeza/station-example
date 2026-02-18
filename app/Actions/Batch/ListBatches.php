<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use Illuminate\Support\Facades\Cache;

class ListBatches
{
    public function handle(?string $connection = null, ?string $status = null): array
    {
        $activeBatches = Cache::get('active_batches', []);
        $batchDetails = [];

        foreach ($activeBatches as $batchId => $batch) {
            if ($connection && ($batch['driver'] ?? '') !== $connection) {
                continue;
            }

            $total = Cache::get("batch:{$batchId}:total", 0);
            $completed = Cache::get("batch:{$batchId}:completed", 0);
            $failed = Cache::get("batch:{$batchId}:failed", 0);
            $pending = $total - $completed - $failed;
            $isComplete = $pending <= 0 && $total > 0;
            $hasFailed = $failed > 0;

            if ($status) {
                $matches = match ($status) {
                    'pending' => !$isComplete,
                    'complete' => $isComplete && !$hasFailed,
                    'failed' => $hasFailed,
                    default => true,
                };
                if (!$matches) {
                    continue;
                }
            }

            $batchDetails[] = [
                'id' => $batchId,
                'driver' => $batch['driver'],
                'queue' => $batch['queue'],
                'total' => $total,
                'completed' => $completed,
                'failed' => $failed,
                'pending' => max(0, $pending),
                'progress' => $total > 0 ? round(($completed + $failed) / $total * 100, 2) : 0,
                'started_at' => $batch['started_at'],
                'is_complete' => $isComplete,
            ];
        }

        usort($batchDetails, static fn($a, $b) => strcmp($b['started_at'], $a['started_at']));

        return [
            'batches' => \array_slice($batchDetails, 0, 20),
        ];
    }
}
