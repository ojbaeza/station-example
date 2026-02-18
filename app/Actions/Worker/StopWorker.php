<?php

declare(strict_types=1);

namespace App\Actions\Worker;

use Station\Core\ProcessManager;

class StopWorker
{
    public function __construct(
        private readonly ProcessManager $processManager,
    ) {}

    public function handle(string $driver): array
    {
        $result = $this->processManager->stopWorker($driver);

        return [
            'success' => $result['success'],
            'driver' => $driver,
            'stopped' => $result['stopped'],
        ];
    }
}
