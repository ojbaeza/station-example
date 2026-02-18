<?php

declare(strict_types=1);

namespace App\Actions\Workflow;

use App\Models\WorkflowDefinition;
use Station\Workflows\WorkflowManager;

class RunWorkflowDefinition
{
    public function __construct(
        private readonly WorkflowManager $workflowManager,
    ) {}

    public function handle(string $name): ?array
    {
        $definition = $this->workflowManager->getDefinition($name);

        if ($definition === null) {
            return null;
        }

        $storedDefinition = WorkflowDefinition::where('name', $name)->first();
        $connection = $storedDefinition?->connection;

        $instance = $this->workflowManager->runAsync($name, [], $connection);

        return [
            'workflow_id' => $instance->getId(),
            'name' => $name,
            'status' => $instance->getStatus(),
        ];
    }
}
