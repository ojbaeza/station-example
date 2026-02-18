<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use App\Jobs\BatchTestJob;
use Station\Core\BatchManager;

class CreateStationBatch
{
    public function __construct(
        private readonly BatchManager $batchManager,
    ) {}

    public function handle(string $name, int $count, string $queue, string $connection, int $allowedFailures, int $failPercentage): array
    {
        $jobs = [];
        for ($i = 1; $i <= $count; $i++) {
            $shouldFail = $failPercentage > 0 && random_int(1, 100) <= $failPercentage;
            $jobs[] = new BatchTestJob($i, $name, $shouldFail);
        }

        $batch = $this->batchManager->create(
            $jobs,
            $name,
            $queue,
            $allowedFailures,
            connection: $connection,
        );

        return [
            'batch_id' => $batch->id,
            'name' => $batch->name,
            'total_jobs' => $batch->totalJobs,
            'queue' => $queue,
            'connection' => $connection,
        ];
    }
}
