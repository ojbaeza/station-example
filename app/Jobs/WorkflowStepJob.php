<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Test job for Station workflows.
 *
 * WorkflowManager instantiates steps with (string $instanceId, array $context, array $results),
 * then sets workflowStepName before calling handle().
 */
class WorkflowStepJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public string $workflowStepName = '';

    public string $workflowInstanceId = '';

    /**
     * @param string $instanceId Workflow instance ID (provided by WorkflowManager)
     * @param array<string, mixed> $context Workflow context (provided by WorkflowManager)
     * @param array<string, mixed> $results Results from completed steps, keyed by step name
     */
    public function __construct(
        public readonly string $instanceId,
        public readonly array $context = [],
        public readonly array $results = [],
    ) {}

    public function handle(): void
    {
        // Read step-specific config from context
        $stepConfig = $this->context['step_configs'][$this->workflowStepName] ?? [];
        $stepName = $stepConfig['step_name'] ?? $this->workflowStepName;
        $processingTimeMs = (int) ($stepConfig['processing_time_ms'] ?? 500);
        $shouldFail = (bool) ($stepConfig['should_fail'] ?? false);

        Log::info("WorkflowStepJob [{$stepName}] started (workflow: {$this->instanceId})");

        // Simulate work
        usleep($processingTimeMs * 1000);

        if ($shouldFail) {
            throw new RuntimeException("WorkflowStepJob [{$stepName}] failed intentionally");
        }

        Log::info("WorkflowStepJob [{$stepName}] completed (workflow: {$this->instanceId})");
    }

    /**
     * Provide context updates back to the workflow.
     *
     * @return array<string, mixed>
     */
    public function getContextUpdates(): array
    {
        return [
            "result_{$this->workflowStepName}" => 'completed',
        ];
    }

    public function tags(): array
    {
        return ['workflow-step', $this->workflowStepName];
    }
}
