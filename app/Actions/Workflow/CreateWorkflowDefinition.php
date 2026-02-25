<?php

declare(strict_types=1);

namespace App\Actions\Workflow;

use App\Jobs\WorkflowStepJob;
use App\Models\WorkflowDefinition;
use Illuminate\Support\Facades\Cache;
use Station\Facades\Workflow;

class CreateWorkflowDefinition
{
    public function handle(array $data): array
    {
        Cache::forget('workflow_definitions');

        $definition = WorkflowDefinition::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'steps' => $data['steps'],
            'timeout' => $data['timeout'],
            'connection' => $data['connection'] ?? null,
        ]);

        $wf = Workflow::define($data['name'])
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
