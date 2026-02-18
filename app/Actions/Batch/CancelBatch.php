<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use Station\Core\BatchManager;

class CancelBatch
{
    public function __construct(
        private readonly BatchManager $batchManager,
    ) {}

    public function handle(string $id): array
    {
        $result = $this->batchManager->cancel($id);

        return [
            'success' => $result,
            'message' => $result ? 'Batch cancelled' : 'Unable to cancel batch',
        ];
    }
}
