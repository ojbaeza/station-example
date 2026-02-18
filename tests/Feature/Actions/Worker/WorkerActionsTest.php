<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Worker;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Station\Core\ProcessManager;
use Tests\TestCase;

class WorkerActionsTest extends TestCase
{
    private MockInterface $processManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->processManager = Mockery::mock(ProcessManager::class);
        $this->app->instance(ProcessManager::class, $this->processManager);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function validConnectionProvider(): array
    {
        return [
            'rabbitmq' => ['rabbitmq'],
            'redis' => ['redis'],
            'sqs' => ['sqs'],
            'beanstalkd' => ['beanstalkd'],
            'kafka' => ['kafka'],
        ];
    }

    // -- LaunchWorker tests --

    public function testLaunchWorkerWithValidDataReturnsSuccess(): void
    {
        $this->processManager->shouldReceive('startSupervisor')
            ->once()
            ->with('rabbitmq', 'default', 2)
            ->andReturn(['success' => true, 'pid' => 1234]);

        $response = $this->postJson('/worker/launch', [
            'connection' => 'rabbitmq',
            'queue' => 'default',
            'workers' => 2,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('connection', 'rabbitmq')
            ->assertJsonPath('queue', 'default')
            ->assertJsonPath('workers', 2)
            ->assertJsonPath('pid', 1234);
    }

    public function testLaunchWorkerValidationRejectsMissingConnection(): void
    {
        $response = $this->postJson('/worker/launch', [
            'queue' => 'default',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['connection']);
    }

    public function testLaunchWorkerValidationRejectsInvalidConnection(): void
    {
        $response = $this->postJson('/worker/launch', [
            'connection' => 'invalid_driver',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['connection']);
    }

    public function testLaunchWorkerValidationRejectsWorkersAboveMaximum(): void
    {
        $response = $this->postJson('/worker/launch', [
            'connection' => 'rabbitmq',
            'workers' => 11,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['workers']);
    }

    public function testLaunchWorkerValidationRejectsWorkersBelowMinimum(): void
    {
        $response = $this->postJson('/worker/launch', [
            'connection' => 'rabbitmq',
            'workers' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['workers']);
    }

    public function testLaunchWorkerHandlesExceptionReturns500(): void
    {
        $this->processManager->shouldReceive('startSupervisor')
            ->andThrow(new RuntimeException('Process management disabled'));

        $response = $this->postJson('/worker/launch', [
            'connection' => 'rabbitmq',
        ]);

        $response->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error', 'Process management disabled');
    }

    #[DataProvider('validConnectionProvider')]
    public function testLaunchWorkerAcceptsAllValidConnections(string $connection): void
    {
        $this->processManager->shouldReceive('startSupervisor')
            ->andReturn(['success' => true, 'pid' => 999]);

        $response = $this->postJson('/worker/launch', [
            'connection' => $connection,
        ]);

        $response->assertStatus(200);
    }

    // -- StartWorker tests --

    public function testStartWorkerWithValidDataReturnsSuccess(): void
    {
        $this->processManager->shouldReceive('startWorker')
            ->once()
            ->with('redis', 'high')
            ->andReturn(['success' => true, 'pid' => 5678]);

        $response = $this->postJson('/worker/start', [
            'driver' => 'redis',
            'queue' => 'high',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('pid', 5678)
            ->assertJsonPath('driver', 'redis')
            ->assertJsonPath('queue', 'high');
    }

    public function testStartWorkerValidationRejectsMissingDriver(): void
    {
        $response = $this->postJson('/worker/start', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['driver']);
    }

    public function testStartWorkerValidationRejectsInvalidDriver(): void
    {
        $response = $this->postJson('/worker/start', [
            'driver' => 'nonexistent',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['driver']);
    }

    public function testStartWorkerHandlesExceptionReturns500(): void
    {
        $this->processManager->shouldReceive('startWorker')
            ->andThrow(new RuntimeException('Cannot start worker'));

        $response = $this->postJson('/worker/start', [
            'driver' => 'rabbitmq',
        ]);

        $response->assertStatus(500)
            ->assertJsonPath('success', false);
    }

    // -- StopWorker tests --

    public function testStopWorkerWithValidDriverReturnsSuccess(): void
    {
        $this->processManager->shouldReceive('stopWorker')
            ->once()
            ->with('rabbitmq')
            ->andReturn(['success' => true, 'stopped' => 2]);

        $response = $this->postJson('/worker/stop', [
            'driver' => 'rabbitmq',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('driver', 'rabbitmq')
            ->assertJsonPath('stopped', 2);
    }

    public function testStopWorkerValidationRejectsMissingDriver(): void
    {
        $response = $this->postJson('/worker/stop', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['driver']);
    }

    public function testStopWorkerHandlesExceptionReturns500(): void
    {
        $this->processManager->shouldReceive('stopWorker')
            ->andThrow(new RuntimeException('Cannot signal process'));

        $response = $this->postJson('/worker/stop', [
            'driver' => 'rabbitmq',
        ]);

        $response->assertStatus(500)
            ->assertJsonPath('success', false);
    }

    // -- StopExternalWorker tests --

    public function testStopExternalWorkerWithValidPidReturnsSuccess(): void
    {
        $this->processManager->shouldReceive('stopExternalWorker')
            ->once()
            ->with(9999)
            ->andReturn(['success' => true, 'pid' => 9999]);

        $response = $this->postJson('/worker/stop-external', [
            'pid' => 9999,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function testStopExternalWorkerValidationRejectsMissingPid(): void
    {
        $response = $this->postJson('/worker/stop-external', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['pid']);
    }

    public function testStopExternalWorkerValidationRejectsZeroPid(): void
    {
        $response = $this->postJson('/worker/stop-external', [
            'pid' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['pid']);
    }

    public function testStopExternalWorkerValidationRejectsNegativePid(): void
    {
        $response = $this->postJson('/worker/stop-external', [
            'pid' => -1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['pid']);
    }

    public function testStopExternalWorkerHandlesExceptionReturns500(): void
    {
        $this->processManager->shouldReceive('stopExternalWorker')
            ->andThrow(new RuntimeException('Process not found'));

        $response = $this->postJson('/worker/stop-external', [
            'pid' => 12345,
        ]);

        $response->assertStatus(500)
            ->assertJsonPath('success', false);
    }

    // -- GetWorkerStatus tests --

    public function testGetWorkerStatusReturnsJsonWithWorkerInfo(): void
    {
        $this->processManager->shouldReceive('getWorkerStatus')
            ->once()
            ->andReturn([
                'rabbitmq' => [
                    'running' => true,
                    'pid' => 1234,
                    'workers' => [
                        ['pid' => 1234, 'role' => 'supervisor', 'queue' => 'default', 'children' => [1235, 1236], 'command' => 'station:work'],
                        ['pid' => 1235, 'queue' => 'default', 'command' => 'station:work'],
                        ['pid' => 1236, 'queue' => 'default', 'command' => 'station:work'],
                    ],
                ],
            ]);

        $response = $this->getJson('/worker/status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'workers' => [
                    'rabbitmq' => ['running', 'pid', 'queue', 'is_supervisor', 'is_station'],
                ],
                'default',
            ])
            ->assertJsonPath('workers.rabbitmq.running', true)
            ->assertJsonPath('workers.rabbitmq.pid', 1234);
    }

    public function testGetWorkerStatusHandlesExceptionGracefully(): void
    {
        $this->processManager->shouldReceive('getWorkerStatus')
            ->andThrow(new RuntimeException('Process check failed'));

        $response = $this->getJson('/worker/status');

        $response->assertStatus(200)
            ->assertJsonPath('workers', [])
            ->assertJsonPath('error', 'Process check failed');
    }

    public function testGetWorkerStatusReturnsDefaultConnection(): void
    {
        $this->processManager->shouldReceive('getWorkerStatus')->andReturn([]);

        $response = $this->getJson('/worker/status');

        $response->assertStatus(200)
            ->assertJsonStructure(['default']);
    }

    public function testGetWorkerStatusIdentifiesExternalWorkers(): void
    {
        $this->processManager->shouldReceive('getWorkerStatus')
            ->once()
            ->andReturn([
                'rabbitmq' => [
                    'running' => true,
                    'pid' => 100,
                    'workers' => [
                        ['pid' => 100, 'role' => 'supervisor', 'queue' => 'default', 'children' => [101], 'command' => 'station:work'],
                        ['pid' => 101, 'queue' => 'default', 'command' => 'station:work'],
                        ['pid' => 200, 'queue' => 'high', 'command' => 'queue:work rabbitmq'],
                        ['pid' => 201, 'queue' => 'low', 'command' => 'station:work --queue=low'],
                    ],
                ],
            ]);

        $response = $this->getJson('/worker/status');

        $response->assertStatus(200)
            ->assertJsonPath('workers.rabbitmq.running', true)
            ->assertJsonPath('workers.rabbitmq.is_supervisor', true)
            ->assertJsonPath('workers.rabbitmq.external_count', 2)
            ->assertJsonPath('workers.rabbitmq.children', [101]);

        // Verify external workers have is_station flag
        $externalWorkers = $response->json('workers.rabbitmq.external_workers');
        $this->assertCount(2, $externalWorkers);

        // External worker running queue:work should not be station
        $nonStationWorker = collect($externalWorkers)->firstWhere('pid', 200);
        $this->assertFalse($nonStationWorker['is_station']);

        // External worker running station:work should be station
        $stationWorker = collect($externalWorkers)->firstWhere('pid', 201);
        $this->assertTrue($stationWorker['is_station']);
    }

    public function testGetWorkerStatusWithNoSupervisorRole(): void
    {
        $this->processManager->shouldReceive('getWorkerStatus')
            ->once()
            ->andReturn([
                'redis' => [
                    'running' => true,
                    'pid' => 500,
                    'workers' => [
                        ['pid' => 500, 'queue' => 'default', 'command' => 'station:work'],
                    ],
                ],
            ]);

        $response = $this->getJson('/worker/status');

        $response->assertStatus(200)
            ->assertJsonPath('workers.redis.running', true)
            ->assertJsonPath('workers.redis.is_supervisor', false)
            ->assertJsonPath('workers.redis.external_count', 0);
    }

    public function testStartWorkerDefaultsQueueToDefault(): void
    {
        $this->processManager->shouldReceive('startWorker')
            ->once()
            ->with('rabbitmq', 'default')
            ->andReturn(['success' => true, 'pid' => 7777]);

        $response = $this->postJson('/worker/start', [
            'driver' => 'rabbitmq',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('queue', 'default');
    }

    public function testStopWorkerValidationRejectsInvalidDriver(): void
    {
        $response = $this->postJson('/worker/stop', [
            'driver' => 'invalid_driver',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['driver']);
    }

    public function testStopExternalWorkerValidationRejectsStringPid(): void
    {
        $response = $this->postJson('/worker/stop-external', [
            'pid' => 'not_a_number',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['pid']);
    }
}
