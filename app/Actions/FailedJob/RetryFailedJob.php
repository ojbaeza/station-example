<?php

declare(strict_types=1);

namespace App\Actions\FailedJob;

use Station\Core\JobManager;

class RetryFailedJob
{
    public function __construct(
        private readonly JobManager $jobManager,
    ) {}

    public function handle(string $uuid): array
    {
        $result = $this->jobManager->retry($uuid);

        return [
            'success' => $result,
            'message' => $result ? 'Job queued for retry' : 'Unable to retry job',
        ];
    }
}
