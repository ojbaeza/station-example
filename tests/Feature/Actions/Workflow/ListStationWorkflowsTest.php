<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Workflow;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ListStationWorkflowsTest extends TestCase
{
    public function testListStationWorkflowsReturnsEmptyListWhenNoRecords(): void
    {
        DB::table('station_workflows')->delete();

        $response = $this->getJson('/api/station/workflows');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'workflows',
                'pagination' => ['current_page', 'last_page', 'total', 'per_page'],
            ])
            ->assertJsonPath('pagination.total', 0);
    }

    public function testListStationWorkflowsReturnsWorkflowRecords(): void
    {
        DB::table('station_workflows')->delete();

        DB::table('station_workflows')->insert([
            'id' => (string) Str::uuid7(),
            'definition_id' => 'order-processing',
            'definition_name' => 'Order Processing',
            'status' => 'running',
            'current_step' => 'charge-payment',
            'input' => json_encode(['order_id' => 123]),
            'context' => json_encode(['connection' => 'rabbitmq', 'queue' => 'default']),
            'results' => json_encode(null),
            'step_statuses' => json_encode([
                'validate-order' => ['status' => 'completed'],
                'charge-payment' => ['status' => 'running'],
            ]),
            'definition_steps' => json_encode(['validate-order', 'charge-payment', 'send-confirmation']),
            'progress' => 33,
            'started_at' => now()->toDateTimeString(),
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        $response = $this->getJson('/api/station/workflows');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'workflows')
            ->assertJsonPath('workflows.0.name', 'Order Processing')
            ->assertJsonPath('workflows.0.status', 'running')
            ->assertJsonPath('workflows.0.progress', 33)
            ->assertJsonPath('workflows.0.current_step', 'charge-payment');
    }

    public function testListStationWorkflowsFiltersByStatus(): void
    {
        DB::table('station_workflows')->delete();

        DB::table('station_workflows')->insert([
            'id' => (string) Str::uuid7(),
            'definition_id' => 'test-wf',
            'definition_name' => 'Running WF',
            'status' => 'running',
            'input' => json_encode([]),
            'context' => json_encode([]),
            'results' => json_encode(null),
            'step_statuses' => json_encode([]),
            'definition_steps' => json_encode([]),
            'progress' => 50,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        DB::table('station_workflows')->insert([
            'id' => (string) Str::uuid7(),
            'definition_id' => 'test-wf',
            'definition_name' => 'Completed WF',
            'status' => 'completed',
            'input' => json_encode([]),
            'context' => json_encode([]),
            'results' => json_encode(['success' => true]),
            'step_statuses' => json_encode([]),
            'definition_steps' => json_encode([]),
            'progress' => 100,
            'completed_at' => now()->toDateTimeString(),
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        $response = $this->getJson('/api/station/workflows?status=completed');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'workflows')
            ->assertJsonPath('workflows.0.name', 'Completed WF');
    }

    public function testListStationWorkflowsPaginatesResults(): void
    {
        DB::table('station_workflows')->delete();

        for ($i = 0; $i < 15; $i++) {
            DB::table('station_workflows')->insert([
                'id' => (string) Str::uuid7(),
                'definition_id' => 'test-wf',
                'definition_name' => "Workflow {$i}",
                'status' => 'completed',
                'input' => json_encode([]),
                'context' => json_encode([]),
                'results' => json_encode(null),
                'step_statuses' => json_encode([]),
                'definition_steps' => json_encode([]),
                'progress' => 100,
                'created_at' => now()->subMinutes($i)->toDateTimeString(),
                'updated_at' => now()->subMinutes($i)->toDateTimeString(),
            ]);
        }

        $response = $this->getJson('/api/station/workflows?page=1');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'workflows')
            ->assertJsonPath('pagination.total', 15)
            ->assertJsonPath('pagination.last_page', 2);
    }

    public function testListStationWorkflowsConnectionFilterWorksAcrossDrivers(): void
    {
        DB::table('station_workflows')->delete();

        DB::table('station_workflows')->insert([
            'id' => (string) Str::uuid7(),
            'definition_id' => 'test-wf',
            'definition_name' => 'Redis WF',
            'status' => 'running',
            'input' => json_encode([]),
            'context' => json_encode(['connection' => 'redis']),
            'results' => json_encode(null),
            'step_statuses' => json_encode([]),
            'definition_steps' => json_encode([]),
            'progress' => 50,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        $response = $this->getJson('/api/station/workflows?connection=redis');

        $response->assertStatus(200)
            ->assertJsonStructure(['workflows', 'pagination'])
            ->assertJsonCount(1, 'workflows')
            ->assertJsonPath('workflows.0.connection', 'redis');

        // Non-matching connection should return empty
        $response = $this->getJson('/api/station/workflows?connection=sqs');
        $response->assertStatus(200)
            ->assertJsonCount(0, 'workflows');
    }

    public function testListStationWorkflowsWithNegativePageDefaultsToOne(): void
    {
        DB::table('station_workflows')->delete();

        DB::table('station_workflows')->insert([
            'id' => (string) Str::uuid7(),
            'definition_id' => 'test-wf',
            'definition_name' => 'Test WF',
            'status' => 'running',
            'input' => json_encode([]),
            'context' => json_encode([]),
            'results' => json_encode(null),
            'step_statuses' => json_encode([]),
            'definition_steps' => json_encode([]),
            'progress' => 50,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        $response = $this->getJson('/api/station/workflows?page=-1');

        $response->assertStatus(200)
            ->assertJsonPath('pagination.current_page', 1)
            ->assertJsonCount(1, 'workflows');
    }

    public function testListStationWorkflowsExtractsConnectionFromContext(): void
    {
        DB::table('station_workflows')->delete();

        DB::table('station_workflows')->insert([
            'id' => (string) Str::uuid7(),
            'definition_id' => 'test-wf',
            'definition_name' => 'Context WF',
            'status' => 'completed',
            'input' => json_encode([]),
            'context' => json_encode(['connection' => 'redis', 'queue' => 'high']),
            'results' => json_encode(null),
            'step_statuses' => json_encode([]),
            'definition_steps' => json_encode([]),
            'progress' => 100,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        $response = $this->getJson('/api/station/workflows');

        $response->assertStatus(200)
            ->assertJsonPath('workflows.0.connection', 'redis')
            ->assertJsonPath('workflows.0.queue', 'high');
    }
}
