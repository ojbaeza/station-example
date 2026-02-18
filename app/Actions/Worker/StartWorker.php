<?php

declare(strict_types=1);

namespace App\Actions\Worker;

use Station\Core\ProcessManager;

class StartWorker
{
    public function __construct(
        private readonly ProcessManager $processManager,
    ) {}

    public function handle(string $driver, string $queue): array
    {
        $result = $this->processManager->startWorker($driver, $queue);

        return [
            'success' => $result['success'],
            'pid' => $result['pid'],
            'driver' => $driver,
            'queue' => $queue,
        ];
    }
}
