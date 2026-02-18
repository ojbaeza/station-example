<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Workflow;

use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Station\Workflows\WorkflowManager;
use Tests\TestCase;

class CancelWorkflowTest extends TestCase
{
    private MockInterface $workflowManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->workflowManager = Mockery::mock(WorkflowManager::class);
        $this->app->instance(WorkflowManager::class, $this->workflowManager);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testCancelWorkflowWithValidIdReturnsSuccess(): void
    {
        $this->workflowManager->shouldReceive('cancel')
            ->once()
            ->with('wf-cancel-123')
            ->andReturn(true);

        $response = $this->postJson('/api/station/workflow/wf-cancel-123/cancel');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Workflow cancelled');
    }

    public function testCancelWorkflowWhenCancelFailsReturnsFailureMessage(): void
    {
        $this->workflowManager->shouldReceive('cancel')
            ->once()
            ->with('wf-no-cancel')
            ->andReturn(false);

        $response = $this->postJson('/api/station/workflow/wf-no-cancel/cancel');

        $response->assertStatus(200)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Unable to cancel workflow');
    }

    public function testCancelWorkflowHandlesExceptionReturns500(): void
    {
        $this->workflowManager->shouldReceive('cancel')
            ->once()
            ->andThrow(new RuntimeException('Workflow not found'));

        $response = $this->postJson('/api/station/workflow/nonexistent/cancel');

        $response->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error', 'Workflow not found');
    }
}
