<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\WorkflowDefinition;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class WorkflowDefinitionTest extends TestCase
{
    public function testWorkflowDefinitionUsesCorrectTable(): void
    {
        $model = new WorkflowDefinition();

        $this->assertSame('station_workflow_definitions', $model->getTable());
    }

    public function testWorkflowDefinitionHasFillableAttributes(): void
    {
        $model = new WorkflowDefinition();

        $this->assertEqualsCanonicalizing(
            ['name', 'description', 'steps', 'timeout', 'connection'],
            $model->getFillable(),
        );
    }

    public function testWorkflowDefinitionCastsStepsToArray(): void
    {
        WorkflowDefinition::where('name', 'cast-test')->delete();

        $def = WorkflowDefinition::create([
            'name' => 'cast-test',
            'description' => 'Testing casts',
            'steps' => [
                ['name' => 'step-one', 'job_class' => null],
                ['name' => 'step-two', 'dependencies' => ['step-one']],
            ],
            'timeout' => 300,
        ]);

        $freshDef = WorkflowDefinition::find($def->id);

        $this->assertIsArray($freshDef->steps);
        $this->assertCount(2, $freshDef->steps);
        $this->assertSame('step-one', $freshDef->steps[0]['name']);
    }

    public function testWorkflowDefinitionCastsTimeoutToInteger(): void
    {
        WorkflowDefinition::where('name', 'timeout-cast')->delete();

        $def = WorkflowDefinition::create([
            'name' => 'timeout-cast',
            'steps' => [['name' => 'step']],
            'timeout' => 600,
        ]);

        $fresh = WorkflowDefinition::find($def->id);

        $this->assertIsInt($fresh->timeout);
        $this->assertSame(600, $fresh->timeout);
    }

    public function testWorkflowDefinitionUsesUuids(): void
    {
        WorkflowDefinition::where('name', 'uuid-test')->delete();

        $def = WorkflowDefinition::create([
            'name' => 'uuid-test',
            'steps' => [['name' => 'step']],
            'timeout' => 300,
        ]);

        $this->assertNotNull($def->id);
        // UUID format: 8-4-4-4-12 hex characters
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $def->id,
        );
    }

    public function testWorkflowDefinitionCanBeCreatedWithNullableFields(): void
    {
        WorkflowDefinition::where('name', 'nullable-test')->delete();

        $def = WorkflowDefinition::create([
            'name' => 'nullable-test',
            'description' => null,
            'steps' => [['name' => 'step']],
            'timeout' => 300,
            'connection' => null,
        ]);

        $fresh = WorkflowDefinition::find($def->id);

        $this->assertNull($fresh->description);
        $this->assertNull($fresh->connection);
    }

    public function testWorkflowDefinitionCanBeUpdated(): void
    {
        WorkflowDefinition::where('name', 'update-test')->delete();
        WorkflowDefinition::where('name', 'updated-name')->delete();

        $def = WorkflowDefinition::create([
            'name' => 'update-test',
            'steps' => [['name' => 'step']],
            'timeout' => 300,
        ]);

        $def->update([
            'name' => 'updated-name',
            'timeout' => 600,
        ]);

        $fresh = WorkflowDefinition::find($def->id);

        $this->assertSame('updated-name', $fresh->name);
        $this->assertSame(600, $fresh->timeout);
    }

    public function testWorkflowDefinitionCanBeDeleted(): void
    {
        WorkflowDefinition::where('name', 'delete-test')->delete();

        $def = WorkflowDefinition::create([
            'name' => 'delete-test',
            'steps' => [['name' => 'step']],
            'timeout' => 300,
        ]);

        $id = $def->id;
        $def->delete();

        $this->assertNull(WorkflowDefinition::find($id));
    }

    public function testWorkflowDefinitionNameIsUnique(): void
    {
        WorkflowDefinition::where('name', 'unique-test')->delete();

        WorkflowDefinition::create([
            'name' => 'unique-test',
            'steps' => [['name' => 'step']],
            'timeout' => 300,
        ]);

        $this->expectException(QueryException::class);

        WorkflowDefinition::create([
            'name' => 'unique-test',
            'steps' => [['name' => 'step']],
            'timeout' => 300,
        ]);
    }
}
