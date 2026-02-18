<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Batch;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class ListStationBatchesTest extends TestCase
{
    public function testListStationBatchesReturnsEmptyListWhenNoRecords(): void
    {
        DB::table('station_batches')->delete();

        $response = $this->getJson('/api/station/batches');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'batches',
                'pagination' => ['current_page', 'last_page', 'total', 'per_page'],
            ])
            ->assertJsonPath('pagination.total', 0);
    }

    public function testListStationBatchesReturnsBatchRecords(): void
    {
        DB::table('station_batches')->delete();

        DB::table('station_batches')->insert([
            'id' => (string) Str::uuid7(),
            'name' => 'Test Batch',
            'queue' => 'default',
            'connection' => 'rabbitmq',
            'status' => 'processing',
            'total_jobs' => 10,
            'pending_jobs' => 5,
            'processed_jobs' => 4,
            'failed_jobs' => 1,
            'allowed_failures' => 0,
            'failed_job_ids' => json_encode([]),
            'options' => json_encode(null),
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        $response = $this->getJson('/api/station/batches');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'batches')
            ->assertJsonPath('batches.0.name', 'Test Batch')
            ->assertJsonPath('batches.0.status', 'processing')
            ->assertJsonPath('batches.0.total_jobs', 10);
    }

    public function testListStationBatchesFiltersByConnection(): void
    {
        DB::table('station_batches')->delete();

        DB::table('station_batches')->insert([
            [
                'id' => (string) Str::uuid7(),
                'name' => 'RabbitMQ Batch',
                'queue' => 'default',
                'connection' => 'rabbitmq',
                'status' => 'processing',
                'total_jobs' => 5,
                'pending_jobs' => 3,
                'processed_jobs' => 2,
                'failed_jobs' => 0,
                'allowed_failures' => 0,
                'failed_job_ids' => json_encode([]),
                'options' => json_encode(null),
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ],
            [
                'id' => (string) Str::uuid7(),
                'name' => 'Redis Batch',
                'queue' => 'default',
                'connection' => 'redis',
                'status' => 'processing',
                'total_jobs' => 3,
                'pending_jobs' => 1,
                'processed_jobs' => 2,
                'failed_jobs' => 0,
                'allowed_failures' => 0,
                'failed_job_ids' => json_encode([]),
                'options' => json_encode(null),
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ],
        ]);

        $response = $this->getJson('/api/station/batches?connection=rabbitmq');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'batches')
            ->assertJsonPath('batches.0.name', 'RabbitMQ Batch');
    }

    public function testListStationBatchesFiltersByStatus(): void
    {
        DB::table('station_batches')->delete();

        DB::table('station_batches')->insert([
            [
                'id' => (string) Str::uuid7(),
                'name' => 'Completed Batch',
                'queue' => 'default',
                'connection' => 'redis',
                'status' => 'completed',
                'total_jobs' => 5,
                'pending_jobs' => 0,
                'processed_jobs' => 5,
                'failed_jobs' => 0,
                'allowed_failures' => 0,
                'failed_job_ids' => json_encode([]),
                'options' => json_encode(null),
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ],
            [
                'id' => (string) Str::uuid7(),
                'name' => 'Failed Batch',
                'queue' => 'default',
                'connection' => 'redis',
                'status' => 'failed',
                'total_jobs' => 5,
                'pending_jobs' => 0,
                'processed_jobs' => 3,
                'failed_jobs' => 2,
                'allowed_failures' => 0,
                'failed_job_ids' => json_encode([]),
                'options' => json_encode(null),
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ],
        ]);

        $response = $this->getJson('/api/station/batches?status=completed');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'batches')
            ->assertJsonPath('batches.0.name', 'Completed Batch');
    }

    public function testListStationBatchesPaginatesResults(): void
    {
        DB::table('station_batches')->delete();

        for ($i = 0; $i < 15; $i++) {
            DB::table('station_batches')->insert([
                'id' => (string) Str::uuid7(),
                'name' => "Batch {$i}",
                'queue' => 'default',
                'connection' => 'rabbitmq',
                'status' => 'completed',
                'total_jobs' => 1,
                'pending_jobs' => 0,
                'processed_jobs' => 1,
                'failed_jobs' => 0,
                'allowed_failures' => 0,
                'failed_job_ids' => json_encode([]),
                'options' => json_encode(null),
                'created_at' => now()->subMinutes($i)->toDateTimeString(),
                'updated_at' => now()->subMinutes($i)->toDateTimeString(),
            ]);
        }

        $response = $this->getJson('/api/station/batches?page=1');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'batches')
            ->assertJsonPath('pagination.current_page', 1)
            ->assertJsonPath('pagination.last_page', 2)
            ->assertJsonPath('pagination.total', 15)
            ->assertJsonPath('pagination.per_page', 10);

        $responsePage2 = $this->getJson('/api/station/batches?page=2');

        $responsePage2->assertStatus(200)
            ->assertJsonCount(5, 'batches')
            ->assertJsonPath('pagination.current_page', 2);
    }

    public function testListStationBatchesCalculatesProgress(): void
    {
        DB::table('station_batches')->delete();

        DB::table('station_batches')->insert([
            'id' => (string) Str::uuid7(),
            'name' => 'Half Done',
            'queue' => 'default',
            'connection' => 'rabbitmq',
            'status' => 'processing',
            'total_jobs' => 100,
            'pending_jobs' => 50,
            'processed_jobs' => 40,
            'failed_jobs' => 10,
            'allowed_failures' => 0,
            'failed_job_ids' => json_encode([]),
            'options' => json_encode(null),
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        $response = $this->getJson('/api/station/batches');

        $response->assertStatus(200)
            ->assertJsonPath('batches.0.progress', 50);
    }

    public function testListStationBatchesWithZeroTotalJobsReturnsZeroProgress(): void
    {
        DB::table('station_batches')->delete();

        DB::table('station_batches')->insert([
            'id' => (string) Str::uuid7(),
            'name' => 'Empty Batch',
            'queue' => 'default',
            'connection' => 'rabbitmq',
            'status' => 'pending',
            'total_jobs' => 0,
            'pending_jobs' => 0,
            'processed_jobs' => 0,
            'failed_jobs' => 0,
            'allowed_failures' => 0,
            'failed_job_ids' => json_encode([]),
            'options' => json_encode(null),
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        $response = $this->getJson('/api/station/batches');

        $response->assertStatus(200)
            ->assertJsonPath('batches.0.progress', 0);
    }

    public function testListStationBatchesWithNegativePageDefaultsToOne(): void
    {
        DB::table('station_batches')->delete();

        DB::table('station_batches')->insert([
            'id' => (string) Str::uuid7(),
            'name' => 'Page Test',
            'queue' => 'default',
            'connection' => 'rabbitmq',
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

        $response = $this->getJson('/api/station/batches?page=-1');

        $response->assertStatus(200)
            ->assertJsonPath('pagination.current_page', 1)
            ->assertJsonCount(1, 'batches');
    }

    public function testListStationBatchesSqlInjectionInConnectionFilterIsHandled(): void
    {
        $response = $this->getJson('/api/station/batches?connection=' . urlencode("'; DROP TABLE station_batches; --"));

        $response->assertStatus(200);

        // Table should still exist
        $this->assertTrue(
            Schema::hasTable('station_batches'),
            'station_batches table should still exist after SQL injection attempt',
        );
    }
}
