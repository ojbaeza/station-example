<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Workflow;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClearStationWorkflowsTest extends TestCase
{
    public function testClearStationWorkflowsDeletesAllRecords(): void
    {
        DB::table('station_workflows')->insert([
            'id' => (string) Str::uuid7(),
            'definition_id' => 'test-wf',
            'definition_name' => 'To Clear',
            'status' => 'completed',
            'input' => json_encode([]),
            'context' => json_encode([]),
            'results' => json_encode(null),
            'step_statuses' => json_encode([]),
            'definition_steps' => json_encode([]),
            'progress' => 100,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        $this->assertGreaterThan(0, DB::table('station_workflows')->count());

        $response = $this->postJson('/api/station/workflows/clear');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertEquals(0, DB::table('station_workflows')->count());
    }

    public function testClearStationWorkflowsSucceedsWhenTableIsEmpty(): void
    {
        DB::table('station_workflows')->delete();

        $response = $this->postJson('/api/station/workflows/clear');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function testClearStationWorkflowsDeletesMultipleRecords(): void
    {
        DB::table('station_workflows')->delete();

        for ($i = 0; $i < 5; $i++) {
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
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ]);
        }

        $this->assertEquals(5, DB::table('station_workflows')->count());

        $response = $this->postJson('/api/station/workflows/clear');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertEquals(0, DB::table('station_workflows')->count());
    }
}
