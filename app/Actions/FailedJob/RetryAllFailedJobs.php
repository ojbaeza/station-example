<?php

declare(strict_types=1);

namespace App\Actions\FailedJob;

use Station\Core\JobManager;

class RetryAllFailedJobs
{
    public function __construct(
        private readonly JobManager $jobManager,
    ) {}

    public function handle(): array
    {
        $count = $this->jobManager->retryAllFailed();

        return [
            'success' => true,
            'message' => "Retried {$count} jobs",
            'retried' => $count,
        ];
    }
}
