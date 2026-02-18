<?php

declare(strict_types=1);

namespace App\Actions\Queue;

use Station\Core\QueueManager;
use Station\Enums\Driver;

class GetQueuePauseStatus
{
    public function __construct(
        private readonly QueueManager $queueManager,
    ) {}

    public function handle(): array
    {
        $drivers = Driver::values();
        $status = [];

        foreach ($drivers as $driver) {
            $status[$driver] = $this->queueManager->isPaused('default', $driver);
        }

        return [
            'paused' => $status,
        ];
    }
}
