<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Batch;

use Illuminate\Support\Facades\Cache;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Station\Core\JobManager;
use Station\Core\PendingDispatch;
use Tests\TestCase;

class DispatchJobsTest extends TestCase
{
    private MockInterface $jobManager;

    protected function setUp(): void
    {
        parent::setUp();

        $pendingDispatch = Mockery::mock(PendingDispatch::class);
        $pendingDispatch->shouldReceive('onConnection')->andReturnSelf();
        $pendingDispatch->shouldReceive('onQueue')->andReturnSelf();
        $pendingDispatch->shouldReceive('delay')->andReturnSelf();
        $pendingDispatch->shouldReceive('dispatch')->andReturn('job-id-123');

        $this->jobManager = Mockery::mock(JobManager::class);
        $this->jobManager->shouldReceive('job')->andReturn($pendingDispatch);

        $this->app->instance(JobManager::class, $this->jobManager);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function validDriverProvider(): array
    {
        return [
            'sync' => ['sync'],
            'rabbitmq' => ['rabbitmq'],
            'redis' => ['redis'],
            'sqs' => ['sqs'],
            'beanstalkd' => ['beanstalkd'],
            'kafka' => ['kafka'],
        ];
    }

    public function testDispatchJobsWithValidDataReturnsSuccess(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'sync',
            'count' => 3,
            'queue' => 'default',
            'delay' => 0,
            'fail_percentage' => 0,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'batch_id',
                'total',
                'driver',
                'queue',
                'delay',
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('total', 3)
            ->assertJsonPath('driver', 'sync')
            ->assertJsonPath('queue', 'default');
    }

    public function testDispatchJobsWithDelayIncludesDelayInResponse(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'rabbitmq',
            'count' => 1,
            'delay' => 30,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('delay', 30);
    }

    public function testDispatchJobsValidationRejectsInvalidDriver(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'nonexistent_driver',
            'count' => 5,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['driver']);
    }

    public function testDispatchJobsValidationRejectsMissingDriver(): void
    {
        $response = $this->postJson('/dispatch', [
            'count' => 5,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['driver']);
    }

    public function testDispatchJobsValidationRejectsMissingCount(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'sync',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['count']);
    }

    public function testDispatchJobsValidationRejectsCountBelowMinimum(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'sync',
            'count' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['count']);
    }

    public function testDispatchJobsValidationRejectsCountAboveMaximum(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'sync',
            'count' => 100001,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['count']);
    }

    public function testDispatchJobsValidationRejectsNegativeDelay(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'sync',
            'count' => 1,
            'delay' => -1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['delay']);
    }

    public function testDispatchJobsValidationRejectsDelayAboveMaximum(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'sync',
            'count' => 1,
            'delay' => 3601,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['delay']);
    }

    public function testDispatchJobsValidationRejectsFailPercentageAboveMaximum(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'sync',
            'count' => 1,
            'fail_percentage' => 101,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['fail_percentage']);
    }

    public function testDispatchJobsAcceptsSyncDriver(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'sync',
            'count' => 1,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('driver', 'sync');
    }

    #[DataProvider('validDriverProvider')]
    public function testDispatchJobsAcceptsValidDrivers(string $driver): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => $driver,
            'count' => 1,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('driver', $driver);
    }

    public function testDispatchJobsDefaultsQueueToDefault(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'sync',
            'count' => 1,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('queue', 'default');
    }

    public function testDispatchJobsDefaultsDelayToZero(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'sync',
            'count' => 1,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('delay', 0);
    }

    public function testDispatchJobsReturnsBatchId(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'sync',
            'count' => 1,
        ]);

        $response->assertStatus(200);
        $batchId = $response->json('batch_id');
        $this->assertNotEmpty($batchId);
        $this->assertIsString($batchId);
    }

    public function testDispatchJobsStoresBatchDataInCache(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'sync',
            'count' => 3,
            'queue' => 'priority',
        ]);

        $response->assertStatus(200);
        $batchId = $response->json('batch_id');

        // Verify all cache keys were created
        $this->assertEquals(3, Cache::get("batch:{$batchId}:total"));
        $this->assertEquals(0, Cache::get("batch:{$batchId}:completed"));
        $this->assertEquals(0, Cache::get("batch:{$batchId}:failed"));
        $this->assertEquals('sync', Cache::get("batch:{$batchId}:driver"));
        $this->assertEquals('priority', Cache::get("batch:{$batchId}:queue"));
        $this->assertNotNull(Cache::get("batch:{$batchId}:started_at"));
    }

    public function testDispatchJobsAddsBatchToActiveBatches(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'sync',
            'count' => 1,
        ]);

        $response->assertStatus(200);
        $batchId = $response->json('batch_id');

        $activeBatches = Cache::get('active_batches', []);
        $this->assertArrayHasKey($batchId, $activeBatches);
        $this->assertEquals('sync', $activeBatches[$batchId]['driver']);
    }

    public function testDispatchJobsWithCustomQueueReturnsCorrectQueue(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'sync',
            'count' => 1,
            'queue' => 'high-priority',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('queue', 'high-priority');
    }

    public function testDispatchJobsValidationRejectsNegativeFailPercentage(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'sync',
            'count' => 1,
            'fail_percentage' => -5,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['fail_percentage']);
    }
}
