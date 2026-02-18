<?php

declare(strict_types=1);

namespace App\Actions\Worker;

use Station\Core\ProcessManager;

class LaunchWorker
{
    public function __construct(
        private readonly ProcessManager $processManager,
    ) {}

    public function handle(string $connection, string $queue, int $workers): array
    {
        $result = $this->processManager->startSupervisor($connection, $queue, $workers);

        return [
            'success' => $result['success'],
            'connection' => $connection,
            'queue' => $queue,
            'workers' => $workers,
            'pid' => $result['pid'],
        ];
    }
}
