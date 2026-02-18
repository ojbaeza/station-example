<?php

declare(strict_types=1);

namespace App\Actions\Driver;

use Station\Contracts\HealthCheckerInterface;

class GetDrivers
{
    public function __construct(
        private readonly HealthCheckerInterface $healthChecker,
    ) {}

    public function handle(): array
    {
        $connectivity = $this->healthChecker->checkConnectivityQuick();

        $drivers = [];
        foreach ($connectivity as $name => $info) {
            $drivers[$name] = array_merge($info->toArray(), [
                'status' => $info->connected ? 'connected' : 'disconnected',
            ]);
        }

        return [
            'drivers' => $drivers,
            'default' => config('queue.default', 'sync'),
        ];
    }
}
