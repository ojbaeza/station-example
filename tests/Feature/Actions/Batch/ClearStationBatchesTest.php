<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Batch;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClearStationBatchesTest extends TestCase
{
    public function testClearStationBatchesDeletesAllRecords(): void
    {
        DB::table('station_batches')->insert([
            'id' => (string) Str::uuid7(),
            'name' => 'To Be Cleared',
            'queue' => 'default',
            'connection' => 'redis',
            'status' => 'completed',
            'total_jobs' => 1,
            'pending_jobs' => 0,
            'processed_jobs' => 1,
            'failed_jobs' => 0,
            'allowed_failures' => 0,
            'failed_job_ids' => json_encode([]),
            'options' => json_encode(null),
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        $this->assertGreaterThan(0, DB::table('station_batches')->count());

        $response = $this->postJson('/api/station/batches/clear');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertEquals(0, DB::table('station_batches')->count());
    }

    public function testClearStationBatchesSucceedsWhenTableIsEmpty(): void
    {
        DB::table('station_batches')->delete();

        $response = $this->postJson('/api/station/batches/clear');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
