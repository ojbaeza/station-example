<?php

declare(strict_types=1);

namespace App\Actions\Worker;

use Station\Core\ProcessManager;

class GetWorkerStatus
{
    public function __construct(
        private readonly ProcessManager $processManager,
    ) {}

    public function handle(): array
    {
        $defaultConnection = config('queue.default', 'sync');
        $libraryStatus = $this->processManager->getWorkerStatus();
        $workers = [];

        foreach ($libraryStatus as $driver => $info) {
            $allProcesses = $info['workers'] ?? [];
            $supervisors = array_filter($allProcesses, static fn($w) => ($w['role'] ?? '') === 'supervisor');
            $firstSupervisor = reset($supervisors) ?: null;

            $mainPid = $info['pid'];
            $isSupervisor = $firstSupervisor !== null && $mainPid === ($firstSupervisor['pid'] ?? null);
            $children = $isSupervisor ? ($firstSupervisor['children'] ?? []) : [];

            $mainQueue = 'default';
            if ($mainPid !== null) {
                foreach ($allProcesses as $p) {
                    if ($p['pid'] === $mainPid) {
                        $mainQueue = $p['queue'] ?? 'default';

                        break;
                    }
                }
            }

            $mainAndChildren = array_merge([$mainPid], $children);
            $externalWorkers = array_values(array_map(
                static fn($w) => array_merge($w, [
                    'is_station' => str_contains($w['command'] ?? '', 'station:work'),
                ]),
                array_filter($allProcesses, static fn($w) => !\in_array($w['pid'], $mainAndChildren, true)),
            ));

            $workers[$driver] = [
                'running' => $info['running'],
                'pid' => $mainPid,
                'queue' => $mainQueue,
                'is_supervisor' => $isSupervisor,
                'is_station' => $info['running'],
                'children' => $children,
                'external_workers' => $externalWorkers,
                'external_count' => \count($externalWorkers),
                'is_default' => $driver === $defaultConnection,
            ];
        }

        return [
            'workers' => $workers,
            'default' => $defaultConnection,
        ];
    }
}
