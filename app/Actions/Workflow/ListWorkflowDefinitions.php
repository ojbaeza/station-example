<?php

declare(strict_types=1);

namespace App\Actions\Workflow;

use App\Models\WorkflowDefinition;
use Station\Workflows\WorkflowManager;

class ListWorkflowDefinitions
{
    public function __construct(
        private readonly WorkflowManager $workflowManager,
    ) {}

    public function handle(): array
    {
        $definitions = array_values(array_map(
            static fn($def) => $def->toArray(),
            $this->workflowManager->getDefinitions(),
        ));

        $dbDefs = WorkflowDefinition::pluck('connection', 'name');

        foreach ($definitions as &$def) {
            $def['connection'] = $dbDefs[$def['name']] ?? null;
        }
        unset($def);

        return [
            'definitions' => $definitions,
        ];
    }
}
