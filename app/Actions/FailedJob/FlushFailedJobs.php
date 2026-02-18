<?php

declare(strict_types=1);

namespace App\Actions\FailedJob;

use Station\Contracts\JobRepositoryInterface;

class FlushFailedJobs
{
    public function __construct(
        private readonly JobRepositoryInterface $jobRepository,
    ) {}

    public function handle(): array
    {
        $deleted = $this->jobRepository->flushFailed();

        return [
            'success' => true,
            'message' => "Deleted {$deleted} failed jobs",
            'deleted' => $deleted,
        ];
    }
}
