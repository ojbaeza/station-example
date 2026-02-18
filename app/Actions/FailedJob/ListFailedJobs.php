<?php

declare(strict_types=1);

namespace App\Actions\FailedJob;

use Illuminate\Support\Str;
use Station\Contracts\JobRepositoryInterface;

class ListFailedJobs
{
    public function __construct(
        private readonly JobRepositoryInterface $jobRepository,
    ) {}

    public function handle(int $page): array
    {
        $perPage = 10;

        $result = $this->jobRepository->paginateFailed([], $page, $perPage);
        $failedJobs = collect($result->data)->map(static fn($job) => [
            'id' => $job->id,
            'uuid' => $job->id,
            'connection' => $job->connection ?? 'unknown',
            'queue' => $job->queue,
            'job_name' => $job->jobClass,
            'exception' => Str::limit($job->payload ?? '', 200),
            'failed_at' => $job->completedAt?->toIso8601String() ?? $job->createdAt?->toIso8601String(),
        ]);

        $total = $result->total;

        return [
            'success' => true,
            'failed_jobs' => $failedJobs,
            'count' => $total,
            'pagination' => [
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
                'total' => $total,
                'per_page' => $perPage,
            ],
        ];
    }
}
