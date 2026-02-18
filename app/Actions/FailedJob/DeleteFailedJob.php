<?php

declare(strict_types=1);

namespace App\Actions\FailedJob;

use Station\Contracts\JobRepositoryInterface;

class DeleteFailedJob
{
    public function __construct(
        private readonly JobRepositoryInterface $jobRepository,
    ) {}

    public function handle(string $uuid): void
    {
        $this->jobRepository->deleteFailed($uuid);
    }
}
