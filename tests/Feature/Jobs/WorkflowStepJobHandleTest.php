<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\WorkflowStepJob;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\TestCase;

class WorkflowStepJobHandleTest extends TestCase
{
    public function testHandleReadsStepConfigFromContext(): void
    {
        $job = new WorkflowStepJob(
            instanceId: 'wf-123',
            context: [
                'step_configs' => [
                    'process_data' => [
                        'step_name' => 'Process Data',
                        'processing_time_ms' => 10,
                        'should_fail' => false,
                    ],
                ],
            ],
        );
        $job->workflowStepName = 'process_data';

        Log::shouldReceive('info')->twice();

        $job->handle();

        $this->assertTrue(true);
    }

    public function testHandleThrowsWhenShouldFailInStepConfig(): void
    {
        $job = new WorkflowStepJob(
            instanceId: 'wf-456',
            context: [
                'step_configs' => [
                    'fail_step' => [
                        'processing_time_ms' => 10,
                        'should_fail' => true,
                    ],
                ],
            ],
        );
        $job->workflowStepName = 'fail_step';

        Log::shouldReceive('info')->once();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('WorkflowStepJob [fail_step] failed intentionally');

        $job->handle();
    }

    public function testHandleCompletesWithEmptyContext(): void
    {
        $job = new WorkflowStepJob(instanceId: 'wf-789');
        $job->workflowStepName = 'default_step';

        Log::shouldReceive('info')->twice();

        $job->handle();

        $this->assertTrue(true);
    }
}
