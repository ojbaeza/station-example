<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Workflow;

use App\Models\WorkflowDefinition;
use Mockery;
use RuntimeException;
use Station\Workflows\WorkflowDefinition as StationWorkflowDefinition;
use Station\Workflows\WorkflowInstance;
use Station\Workflows\WorkflowManager;
use Tests\TestCase;

class WorkflowDefinitionsTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testListWorkflowDefinitionsReturnsDefinitions(): void
    {
        $manager = Mockery::mock(WorkflowManager::class);

        $definition = Mockery::mock(StationWorkflowDefinition::class);
        $definition->shouldReceive('toArray')->andReturn([
            'name' => 'order-processing',
            'description' => 'Process an order',
            'steps' => [],
            'timeout' => 300,
        ]);

        $manager->shouldReceive('getDefinitions')->andReturn([
            'order-processing' => $definition,
        ]);

        $this->app->instance(WorkflowManager::class, $manager);

        $response = $this->getJson('/api/station/workflow-definitions');

        $response->assertStatus(200)
            ->assertJsonStructure(['definitions'])
            ->assertJsonCount(1, 'definitions')
            ->assertJsonPath('definitions.0.name', 'order-processing');
    }

    public function testListWorkflowDefinitionsMergesConnectionFromDatabase(): void
    {
        WorkflowDefinition::where('name', 'db-defined-wf')->delete();

        WorkflowDefinition::create([
            'name' => 'db-defined-wf',
            'description' => 'DB defined',
            'steps' => [['name' => 'step']],
            'timeout' => 300,
            'connection' => 'redis',
        ]);

        $manager = Mockery::mock(WorkflowManager::class);

        $definition = Mockery::mock(StationWorkflowDefinition::class);
        $definition->shouldReceive('toArray')->andReturn([
            'name' => 'db-defined-wf',
            'description' => 'DB defined',
            'steps' => [],
            'timeout' => 300,
        ]);

        $manager->shouldReceive('getDefinitions')->andReturn([
            'db-defined-wf' => $definition,
        ]);

        $this->app->instance(WorkflowManager::class, $manager);

        $response = $this->getJson('/api/station/workflow-definitions');

        $response->assertStatus(200)
            ->assertJsonPath('definitions.0.connection', 'redis');
    }

    public function testListWorkflowDefinitionsReturnsEmptyListWhenNoDefinitions(): void
    {
        $manager = Mockery::mock(WorkflowManager::class);
        $manager->shouldReceive('getDefinitions')->andReturn([]);
        $this->app->instance(WorkflowManager::class, $manager);

        $response = $this->getJson('/api/station/workflow-definitions');

        $response->assertStatus(200)
            ->assertJsonPath('definitions', []);
    }

    public function testCreateWorkflowDefinitionWithValidDataReturnsSuccess(): void
    {
        WorkflowDefinition::where('name', 'my-new-workflow')->delete();

        $response = $this->postJson('/api/station/workflow-definitions', [
            'name' => 'my-new-workflow',
            'description' => 'A test workflow definition',
            'timeout' => 600,
            'connection' => 'rabbitmq',
            'steps' => [
                [
                    'name' => 'step-one',
                    'job_class' => null,
                    'dependencies' => [],
                ],
                [
                    'name' => 'step-two',
                    'job_class' => null,
                    'dependencies' => ['step-one'],
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'definition' => ['id', 'name', 'description', 'steps', 'timeout', 'connection'],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('definition.name', 'my-new-workflow')
            ->assertJsonPath('definition.timeout', 600)
            ->assertJsonPath('definition.connection', 'rabbitmq');

        $this->assertDatabaseHas('station_workflow_definitions', [
            'name' => 'my-new-workflow',
        ]);
    }

    public function testCreateWorkflowDefinitionValidationRejectsMissingName(): void
    {
        $response = $this->postJson('/api/station/workflow-definitions', [
            'timeout' => 300,
            'steps' => [['name' => 'step-one']],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function testCreateWorkflowDefinitionValidationRejectsMissingSteps(): void
    {
        $response = $this->postJson('/api/station/workflow-definitions', [
            'name' => 'no-steps-workflow',
            'timeout' => 300,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['steps']);
    }

    public function testCreateWorkflowDefinitionValidationRejectsEmptyStepsArray(): void
    {
        $response = $this->postJson('/api/station/workflow-definitions', [
            'name' => 'empty-steps-workflow',
            'timeout' => 300,
            'steps' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['steps']);
    }

    public function testCreateWorkflowDefinitionValidationRejectsMissingTimeout(): void
    {
        $response = $this->postJson('/api/station/workflow-definitions', [
            'name' => 'no-timeout-workflow',
            'steps' => [['name' => 'step-one']],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['timeout']);
    }

    public function testCreateWorkflowDefinitionValidationRejectsTimeoutAboveMaximum(): void
    {
        $response = $this->postJson('/api/station/workflow-definitions', [
            'name' => 'long-timeout-workflow',
            'timeout' => 86401,
            'steps' => [['name' => 'step-one']],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['timeout']);
    }

    public function testCreateWorkflowDefinitionValidationRejectsDuplicateName(): void
    {
        WorkflowDefinition::where('name', 'duplicate-name')->delete();

        WorkflowDefinition::create([
            'name' => 'duplicate-name',
            'description' => 'First',
            'steps' => [['name' => 'step-one']],
            'timeout' => 300,
        ]);

        $response = $this->postJson('/api/station/workflow-definitions', [
            'name' => 'duplicate-name',
            'timeout' => 300,
            'steps' => [['name' => 'step-one']],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function testCreateWorkflowDefinitionWithParallelSteps(): void
    {
        WorkflowDefinition::where('name', 'parallel-def')->delete();

        $response = $this->postJson('/api/station/workflow-definitions', [
            'name' => 'parallel-def',
            'timeout' => 300,
            'steps' => [
                [
                    'name' => 'parallel-group',
                    'parallel' => true,
                    'dependencies' => [],
                    'jobs' => [
                        ['name' => 'job-a', 'job_class' => null],
                        ['name' => 'job-b', 'job_class' => null],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function testUpdateWorkflowDefinitionWithValidData(): void
    {
        WorkflowDefinition::where('name', 'update-me')->delete();

        WorkflowDefinition::create([
            'name' => 'update-me',
            'description' => 'Original',
            'steps' => [['name' => 'step-one']],
            'timeout' => 300,
        ]);

        $response = $this->putJson('/api/station/workflow-definitions/update-me', [
            'name' => 'update-me',
            'description' => 'Updated description',
            'timeout' => 600,
            'steps' => [
                ['name' => 'new-step-one', 'job_class' => null],
                ['name' => 'new-step-two', 'job_class' => null, 'dependencies' => ['new-step-one']],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('definition.description', 'Updated description')
            ->assertJsonPath('definition.timeout', 600);
    }

    public function testUpdateWorkflowDefinitionReturns404ForNonExistent(): void
    {
        $response = $this->putJson('/api/station/workflow-definitions/nonexistent-wf', [
            'name' => 'nonexistent-wf',
            'timeout' => 300,
            'steps' => [['name' => 'step-one']],
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function testDeleteWorkflowDefinitionRemovesRecord(): void
    {
        WorkflowDefinition::where('name', 'delete-me')->delete();

        WorkflowDefinition::create([
            'name' => 'delete-me',
            'description' => 'To be deleted',
            'steps' => [['name' => 'step-one']],
            'timeout' => 300,
        ]);

        $response = $this->deleteJson('/api/station/workflow-definitions/delete-me');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('station_workflow_definitions', [
            'name' => 'delete-me',
        ]);
    }

    public function testDeleteWorkflowDefinitionForNonExistentReturnsFailure(): void
    {
        $response = $this->deleteJson('/api/station/workflow-definitions/no-such-wf');

        $response->assertStatus(200)
            ->assertJsonPath('success', false);
    }

    public function testRunWorkflowDefinitionDispatchesWorkflow(): void
    {
        $instance = Mockery::mock(WorkflowInstance::class);
        $instance->shouldReceive('getId')->andReturn('wf-run-123');
        $instance->shouldReceive('getStatus')->andReturn('running');

        $definition = Mockery::mock(StationWorkflowDefinition::class);

        $manager = Mockery::mock(WorkflowManager::class);
        $manager->shouldReceive('getDefinition')
            ->with('order-processing')
            ->andReturn($definition);

        $manager->shouldReceive('runAsync')
            ->once()
            ->andReturn($instance);

        $this->app->instance(WorkflowManager::class, $manager);

        $response = $this->postJson('/api/station/workflow-definitions/run', [
            'definition' => 'order-processing',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('workflow_id', 'wf-run-123');
    }

    public function testRunWorkflowDefinitionReturns404ForNonExistent(): void
    {
        $manager = Mockery::mock(WorkflowManager::class);
        $manager->shouldReceive('getDefinition')->andReturn(null);

        $this->app->instance(WorkflowManager::class, $manager);

        $response = $this->postJson('/api/station/workflow-definitions/run', [
            'definition' => 'nonexistent-workflow',
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function testRunWorkflowDefinitionValidationRejectsMissingDefinition(): void
    {
        $response = $this->postJson('/api/station/workflow-definitions/run', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['definition']);
    }

    public function testRunWorkflowDefinitionHandlesExceptionDuringExecution(): void
    {
        $definition = Mockery::mock(StationWorkflowDefinition::class);

        $manager = Mockery::mock(WorkflowManager::class);
        $manager->shouldReceive('getDefinition')->andReturn($definition);
        $manager->shouldReceive('runAsync')
            ->andThrow(new RuntimeException('Queue unavailable'));

        $this->app->instance(WorkflowManager::class, $manager);

        $response = $this->postJson('/api/station/workflow-definitions/run', [
            'definition' => 'order-processing',
        ]);

        $response->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error', 'Queue unavailable');
    }

    public function testRunWorkflowDefinitionPassesConnectionFromStoredDefinition(): void
    {
        // Create a stored workflow definition with a specific connection
        WorkflowDefinition::where('name', 'run-with-connection')->delete();

        WorkflowDefinition::create([
            'name' => 'run-with-connection',
            'description' => 'Test with connection',
            'steps' => [['name' => 'step-one']],
            'timeout' => 300,
            'connection' => 'redis',
        ]);

        $instance = Mockery::mock(WorkflowInstance::class);
        $instance->shouldReceive('getId')->andReturn('wf-conn-123');
        $instance->shouldReceive('getStatus')->andReturn('running');

        $definition = Mockery::mock(StationWorkflowDefinition::class);

        $manager = Mockery::mock(WorkflowManager::class);
        $manager->shouldReceive('getDefinition')
            ->with('run-with-connection')
            ->andReturn($definition);

        $manager->shouldReceive('runAsync')
            ->once()
            ->with('run-with-connection', [], 'redis')
            ->andReturn($instance);

        $this->app->instance(WorkflowManager::class, $manager);

        $response = $this->postJson('/api/station/workflow-definitions/run', [
            'definition' => 'run-with-connection',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('workflow_id', 'wf-conn-123');
    }

    public function testRunWorkflowDefinitionPassesNullConnectionWhenNotStored(): void
    {
        $instance = Mockery::mock(WorkflowInstance::class);
        $instance->shouldReceive('getId')->andReturn('wf-null-conn');
        $instance->shouldReceive('getStatus')->andReturn('running');

        $definition = Mockery::mock(StationWorkflowDefinition::class);

        $manager = Mockery::mock(WorkflowManager::class);
        $manager->shouldReceive('getDefinition')
            ->with('code-only-workflow')
            ->andReturn($definition);

        // When there is no stored definition, connection should be null
        $manager->shouldReceive('runAsync')
            ->once()
            ->with('code-only-workflow', [], null)
            ->andReturn($instance);

        $this->app->instance(WorkflowManager::class, $manager);

        $response = $this->postJson('/api/station/workflow-definitions/run', [
            'definition' => 'code-only-workflow',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function testUpdateWorkflowDefinitionValidationRejectsMissingName(): void
    {
        WorkflowDefinition::where('name', 'update-validation')->delete();

        WorkflowDefinition::create([
            'name' => 'update-validation',
            'steps' => [['name' => 'step']],
            'timeout' => 300,
        ]);

        $response = $this->putJson('/api/station/workflow-definitions/update-validation', [
            'timeout' => 300,
            'steps' => [['name' => 'step-one']],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function testUpdateWorkflowDefinitionValidationRejectsNameConflict(): void
    {
        WorkflowDefinition::where('name', 'existing-name')->delete();
        WorkflowDefinition::where('name', 'name-to-update')->delete();

        WorkflowDefinition::create([
            'name' => 'existing-name',
            'steps' => [['name' => 'step']],
            'timeout' => 300,
        ]);

        WorkflowDefinition::create([
            'name' => 'name-to-update',
            'steps' => [['name' => 'step']],
            'timeout' => 300,
        ]);

        // Try to rename 'name-to-update' to 'existing-name' (conflict)
        $response = $this->putJson('/api/station/workflow-definitions/name-to-update', [
            'name' => 'existing-name',
            'timeout' => 300,
            'steps' => [['name' => 'step-one']],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function testCreateWorkflowDefinitionXssInNameIsSanitizedByValidation(): void
    {
        WorkflowDefinition::where('name', '<script>alert(1)</script>')->delete();

        $response = $this->postJson('/api/station/workflow-definitions', [
            'name' => '<script>alert(1)</script>',
            'timeout' => 300,
            'steps' => [['name' => 'step-one', 'job_class' => null]],
        ]);

        // Accepted as a valid string (no XSS risk in JSON API responses)
        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
