<?php

declare(strict_types=1);

namespace App\Actions\Workflow;

use App\Models\WorkflowDefinition;

class DeleteWorkflowDefinition
{
    public function handle(string $name): bool
    {
        return WorkflowDefinition::where('name', $name)->delete() > 0;
    }
}
