<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use Station\Core\BatchManager;

class RetryBatch
{
    public function __construct(
        private readonly BatchManager $batchManager,
    ) {}

    public function handle(string $id): array
    {
        $retried = $this->batchManager->retryFailed($id);

        return [
            'success' => true,
            'message' => "Retried {$retried} failed jobs",
            'retried' => $retried,
        ];
    }
}
