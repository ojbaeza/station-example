<?php

declare(strict_types=1);

namespace App\Actions\Queue;

use Station\Core\QueueManager;

class PauseQueue
{
    public function __construct(
        private readonly QueueManager $queueManager,
    ) {}

    public function handle(string $driver, string $queue): array
    {
        $this->queueManager->pause($queue, $driver);

        return [
            'success' => true,
            'driver' => $driver,
            'queue' => $queue,
            'paused' => true,
        ];
    }
}
