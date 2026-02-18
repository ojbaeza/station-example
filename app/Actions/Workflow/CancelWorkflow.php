<?php

declare(strict_types=1);

namespace App\Actions\Workflow;

use Station\Workflows\WorkflowManager;

class CancelWorkflow
{
    public function __construct(
        private readonly WorkflowManager $workflowManager,
    ) {}

    public function handle(string $id): array
    {
        $result = $this->workflowManager->cancel($id);

        return [
            'success' => $result,
            'message' => $result ? 'Workflow cancelled' : 'Unable to cancel workflow',
        ];
    }
}
