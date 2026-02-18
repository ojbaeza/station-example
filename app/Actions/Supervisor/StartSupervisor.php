<?php

declare(strict_types=1);

namespace App\Actions\Supervisor;

use Station\Core\ProcessManager;

class StartSupervisor
{
    public function __construct(
        private readonly ProcessManager $processManager,
    ) {}

    public function handle(string $connection, string $queue, int $workers): array
    {
        return $this->processManager->startSupervisor($connection, $queue, $workers);
    }
}
