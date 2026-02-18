<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Workflow;

use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Station\Workflows\WorkflowDefinition;
use Station\Workflows\WorkflowInstance;
use Station\Workflows\WorkflowManager;
use Tests\TestCase;

class CreateWorkflowTest extends TestCase
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

    public function testCreateWorkflowWithSequentialStepsReturnsSuccess(): void
    {
        $definition = Mockery::mock(WorkflowDefinition::class);
        $definition->shouldReceive('addStep')->andReturnSelf();
        $definition->shouldReceive('addParallel')->andReturnSelf();

        $instance = Mockery::mock(WorkflowInstance::class);
        $instance->shouldReceive('getId')->andReturn('wf-instance-123');
        $instance->shouldReceive('getStatus')->andReturn('running');

        $this->workflowManager->shouldReceive('define')
            ->once()
            ->andReturn($definition);

        $this->workflowManager->shouldReceive('run')
            ->once()
            ->andReturn($instance);

        $response = $this->postJson('/api/station/workflow', [
            'name' => 'My Test Workflow',
            'steps' => 3,
            'queue' => 'default',
            'connection' => 'rabbitmq',
            'parallel_steps' => false,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'workflow_id',
                'name',
                'steps',
                'status',
                'queue',
                'connection',
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('workflow_id', 'wf-instance-123')
            ->assertJsonPath('name', 'My Test Workflow')
            ->assertJsonPath('steps', 3);
    }

    public function testCreateWorkflowWithParallelStepsReturnsSuccess(): void
    {
        $definition = Mockery::mock(WorkflowDefinition::class);
        $definition->shouldReceive('addStep')->andReturnSelf();
        $definition->shouldReceive('addParallel')->andReturnSelf();

        $instance = Mockery::mock(WorkflowInstance::class);
        $instance->shouldReceive('getId')->andReturn('wf-parallel-456');
        $instance->shouldReceive('getStatus')->andReturn('running');

        $this->workflowManager->shouldReceive('define')->andReturn($definition);
        $this->workflowManager->shouldReceive('run')->andReturn($instance);

        $response = $this->postJson('/api/station/workflow', [
            'steps' => 5,
            'parallel_steps' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('steps', 5);
    }

    public function testCreateWorkflowValidationRejectsMissingSteps(): void
    {
        $response = $this->postJson('/api/station/workflow', [
            'name' => 'Test',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['steps']);
    }

    public function testCreateWorkflowValidationRejectsStepsBelowMinimum(): void
    {
        $response = $this->postJson('/api/station/workflow', [
            'steps' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['steps']);
    }

    public function testCreateWorkflowValidationRejectsStepsAboveMaximum(): void
    {
        $response = $this->postJson('/api/station/workflow', [
            'steps' => 21,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['steps']);
    }

    public function testCreateWorkflowValidationRejectsLongName(): void
    {
        $response = $this->postJson('/api/station/workflow', [
            'name' => str_repeat('x', 256),
            'steps' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function testCreateWorkflowHandlesManagerException(): void
    {
        $definition = Mockery::mock(WorkflowDefinition::class);
        $definition->shouldReceive('addStep')->andReturnSelf();

        $this->workflowManager->shouldReceive('define')->andReturn($definition);
        $this->workflowManager->shouldReceive('run')
            ->andThrow(new RuntimeException('Workflow execution failed'));

        $response = $this->postJson('/api/station/workflow', [
            'steps' => 2,
        ]);

        $response->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error', 'Workflow execution failed');
    }

    public function testCreateWorkflowWithDefaultValuesWhenOptionalFieldsOmitted(): void
    {
        $definition = Mockery::mock(WorkflowDefinition::class);
        $definition->shouldReceive('addStep')->andReturnSelf();

        $instance = Mockery::mock(WorkflowInstance::class);
        $instance->shouldReceive('getId')->andReturn('wf-defaults');
        $instance->shouldReceive('getStatus')->andReturn('running');

        $this->workflowManager->shouldReceive('define')->andReturn($definition);
        $this->workflowManager->shouldReceive('run')->andReturn($instance);

        $response = $this->postJson('/api/station/workflow', [
            'steps' => 1,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('queue', 'default');
    }

    public function testCreateWorkflowValidationRejectsLongQueueName(): void
    {
        $response = $this->postJson('/api/station/workflow', [
            'steps' => 1,
            'queue' => str_repeat('x', 256),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['queue']);
    }

    public function testCreateWorkflowValidationRejectsLongConnectionName(): void
    {
        $response = $this->postJson('/api/station/workflow', [
            'steps' => 1,
            'connection' => str_repeat('x', 256),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['connection']);
    }

    public function testCreateWorkflowWithSingleStepReturnsSuccess(): void
    {
        $definition = Mockery::mock(WorkflowDefinition::class);
        $definition->shouldReceive('addStep')->once()->andReturnSelf();

        $instance = Mockery::mock(WorkflowInstance::class);
        $instance->shouldReceive('getId')->andReturn('wf-single');
        $instance->shouldReceive('getStatus')->andReturn('running');

        $this->workflowManager->shouldReceive('define')->andReturn($definition);
        $this->workflowManager->shouldReceive('run')->andReturn($instance);

        $response = $this->postJson('/api/station/workflow', [
            'steps' => 1,
            'parallel_steps' => false,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('steps', 1);
    }

    public function testCreateWorkflowWithMaxStepsReturnsSuccess(): void
    {
        $definition = Mockery::mock(WorkflowDefinition::class);
        $definition->shouldReceive('addStep')->andReturnSelf();

        $instance = Mockery::mock(WorkflowInstance::class);
        $instance->shouldReceive('getId')->andReturn('wf-max');
        $instance->shouldReceive('getStatus')->andReturn('running');

        $this->workflowManager->shouldReceive('define')->andReturn($definition);
        $this->workflowManager->shouldReceive('run')->andReturn($instance);

        $response = $this->postJson('/api/station/workflow', [
            'steps' => 20,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('steps', 20);
    }
}
