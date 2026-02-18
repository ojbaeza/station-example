<?php

declare(strict_types=1);

namespace App\Actions\Worker;

use Station\Core\ProcessManager;

class StopExternalWorker
{
    public function __construct(
        private readonly ProcessManager $processManager,
    ) {}

    public function handle(int $pid): array
    {
        return $this->processManager->stopExternalWorker($pid);
    }
}
