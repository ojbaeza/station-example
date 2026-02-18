<?php

declare(strict_types=1);

namespace App\Actions\Workflow;

use App\Jobs\WorkflowStepJob;
use App\Models\WorkflowDefinition;
use Station\Workflows\WorkflowManager;

class UpdateWorkflowDefinition
{
    public function __construct(
        private readonly WorkflowManager $workflowManager,
    ) {}

    public function handle(string $name, array $data): ?array
    {
        $definition = WorkflowDefinition::where('name', $name)->first();

        if (!$definition) {
            return null;
        }

        $definition->update($data);

        $wf = $this->workflowManager->define($data['name'])
            ->description($data['description'] ?? '')
            ->timeout($data['timeout'])
            ->source('database');

        foreach ($data['steps'] as $step) {
            if (!empty($step['parallel'])) {
                $jobs = [];
                foreach ($step['jobs'] ?? [] as $s) {
                    $jobs[$s['name']] = $s['job_class'] ?: WorkflowStepJob::class;
                }
                $wf->addParallel($step['name'], $jobs, $step['dependencies'] ?? []);
            } else {
                $wf->addStep(
                    $step['name'],
                    $step['job_class'] ?: WorkflowStepJob::class,
                    $step['dependencies'] ?? [],
                );
            }
        }

        return [
            'id' => $definition->id,
            'name' => $definition->name,
            'description' => $definition->description,
            'steps' => $definition->steps,
            'timeout' => $definition->timeout,
            'connection' => $definition->connection,
        ];
    }
}
