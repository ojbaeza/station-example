<?php

declare(strict_types=1);

namespace App\Actions\Supervisor;

use Station\Core\ProcessManager;

class StopSupervisor
{
    public function __construct(
        private readonly ProcessManager $processManager,
    ) {}

    public function handle(): array
    {
        return $this->processManager->stopSupervisor();
    }
}
