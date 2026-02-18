<?php

declare(strict_types=1);

namespace App\Actions\Workflow;

use App\Jobs\WorkflowStepJob;
use Station\Workflows\WorkflowManager;

class CreateWorkflow
{
    public function __construct(
        private readonly WorkflowManager $workflowManager,
    ) {}

    public function handle(string $name, int $stepCount, string $queue, string $connection, bool $parallel): array
    {
        $definition = $this->workflowManager->define($name);

        $stepConfigs = [];

        if ($parallel) {
            $parallelJobs = [];
            for ($i = 1; $i <= $stepCount; $i++) {
                $parallelJobs["step_{$i}"] = WorkflowStepJob::class;
                $stepConfigs["step_{$i}"] = [
                    'step_name' => "Step {$i}",
                    'processing_time_ms' => random_int(200, 800),
                ];
            }
            $definition->addParallel('parallel_steps', $parallelJobs);
        } else {
            $deps = [];
            for ($i = 1; $i <= $stepCount; $i++) {
                $definition->addStep("step_{$i}", WorkflowStepJob::class, $deps);
                $stepConfigs["step_{$i}"] = [
                    'step_name' => "Step {$i}",
                    'processing_time_ms' => random_int(200, 800),
                ];
                $deps = ["step_{$i}"];
            }
        }

        $instance = $this->workflowManager->run($name, [
            'created_at' => now()->toIso8601String(),
            'queue' => $queue,
            'connection' => $connection,
            'step_configs' => $stepConfigs,
        ]);

        return [
            'workflow_id' => $instance->getId(),
            'name' => $name,
            'steps' => $stepCount,
            'status' => $instance->getStatus(),
            'queue' => $queue,
            'connection' => $connection,
        ];
    }
}
