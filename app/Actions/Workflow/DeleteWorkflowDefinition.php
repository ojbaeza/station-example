<?php

declare(strict_types=1);

namespace App\Actions\Workflow;

use App\Models\WorkflowDefinition;
use Illuminate\Support\Facades\Cache;

class DeleteWorkflowDefinition
{
    public function handle(string $name): bool
    {
        Cache::forget('workflow_definitions');

        return WorkflowDefinition::where('name', $name)->delete() > 0;
    }
}
