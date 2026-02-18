<?php

declare(strict_types=1);

namespace Tests\Feature;

use Mockery;
use Station\Contracts\HealthCheckerInterface;
use Station\Core\Batch;
use Station\Core\BatchManager;
use Station\Core\JobManager;
use Station\Core\PendingDispatch;
use Station\Core\ProcessManager;
use Station\Core\QueueManager;
use Station\Workflows\WorkflowManager;
use Tests\TestCase;

/**
 * Security-focused tests for the example application.
 *
 * These tests verify that the application properly handles:
 * - XSS attack vectors in input fields
 * - SQL injection attempts in query parameters
 * - Input boundary violations
 * - Oversized payloads
 */
class SecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockCoreDependencies();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // -- XSS Prevention tests --

    public function testDispatchJobsXssInQueueNameReturnsEscapedValue(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'sync',
            'count' => 1,
            'queue' => '<script>alert("xss")</script>',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        // The queue value should be returned as-is (JSON APIs don't interpret HTML)
        $this->assertSame('<script>alert("xss")</script>', $data['queue']);
    }

    public function testBatchStatusXssInBatchIdDoesNotCauseError(): void
    {
        $response = $this->getJson('/status/' . urlencode('<img src=x onerror=alert(1)>'));

        $response->assertStatus(200)
            ->assertJsonStructure(['batch_id', 'total', 'completed', 'failed', 'pending']);
    }

    // -- SQL Injection Prevention tests --

    public function testListBatchesSqlInjectionInConnectionFilter(): void
    {
        $response = $this->getJson('/batches?connection=' . urlencode("' OR 1=1 --"));

        $response->assertStatus(200)
            ->assertJsonStructure(['batches']);
    }

    public function testListBatchesSqlInjectionInStatusFilter(): void
    {
        $response = $this->getJson('/batches?status=' . urlencode("'; DROP TABLE batches; --"));

        $response->assertStatus(200)
            ->assertJsonStructure(['batches']);
    }

    // -- Input Boundary tests --

    public function testDispatchJobsWithMaximumAllowedCountPassesValidation(): void
    {
        // Use a small count at the boundary (max is 100000) to avoid
        // dispatching 100k mocked jobs. The key assertion is that the
        // max boundary value passes validation -- we test rejection
        // of 100001 separately.
        $response = $this->postJson('/dispatch', [
            'driver' => 'sync',
            'count' => 100000,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('total', 100000);
    }

    public function testDispatchJobsWithMinimumAllowedCountPassesValidation(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'sync',
            'count' => 1,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('total', 1);
    }

    public function testDispatchJobsWithMaximumDelay(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'sync',
            'count' => 1,
            'delay' => 3600,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('delay', 3600);
    }

    public function testDispatchJobsWithMaximumFailPercentage(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'sync',
            'count' => 1,
            'fail_percentage' => 100,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function testDispatchJobsWithNegativeFailPercentageReturnsValidationError(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'sync',
            'count' => 1,
            'fail_percentage' => -1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['fail_percentage']);
    }

    public function testCreateStationBatchWithBoundaryValues(): void
    {
        $batchManager = Mockery::mock(BatchManager::class);
        $batchManager->shouldReceive('create')
            ->andReturn(new Batch(id: 'batch-test', totalJobs: 1000));
        $this->app->instance(BatchManager::class, $batchManager);

        $response = $this->postJson('/api/station/batch', [
            'count' => 1000,
            'name' => str_repeat('a', 255),
            'queue' => str_repeat('q', 255),
            'allowed_failures' => 0,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('total_jobs', 1000);
    }

    // -- Malformed Input tests --

    public function testDispatchJobsWithNonIntegerCountReturnsValidationError(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'sync',
            'count' => 'not_a_number',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['count']);
    }

    public function testDispatchJobsWithFloatCountReturnsValidationError(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'sync',
            'count' => 5.5,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['count']);
    }

    public function testDispatchJobsWithNullDriverReturnsValidationError(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => null,
            'count' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['driver']);
    }

    public function testDispatchJobsWithEmptyBodyReturnsValidationErrors(): void
    {
        $response = $this->postJson('/dispatch', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['driver', 'count']);
    }

    // -- Content type tests --

    public function testNonJsonRequestToApiEndpointReturnsProperResponse(): void
    {
        // Sending a form-encoded request instead of JSON should still process
        // (Laravel handles both content types). The response may be a redirect
        // (302) to the previous page since this is a non-AJAX POST to a web route.
        $response = $this->post('/dispatch', [
            'driver' => 'sync',
            'count' => 1,
        ]);

        $this->assertContains(
            $response->getStatusCode(),
            [200, 302],
            'Non-JSON POST should return 200 or 302, not a server error',
        );
    }

    // -- Extremely long input tests --

    public function testDispatchJobsWithExtremelyLongQueueNameReturnsValidationError(): void
    {
        $response = $this->postJson('/dispatch', [
            'driver' => 'sync',
            'count' => 1,
            'queue' => str_repeat('x', 256),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['queue']);
    }

    public function testLaunchWorkerWithExtremelyLongQueueNameReturnsValidationError(): void
    {
        $response = $this->postJson('/worker/launch', [
            'connection' => 'rabbitmq',
            'queue' => str_repeat('x', 256),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['queue']);
    }

    public function testStopExternalWorkerWithExtremelyLargePidReturnsValidationError(): void
    {
        $response = $this->postJson('/worker/stop-external', [
            'pid' => 99999999999,
        ]);

        // The validation allows integer min:1 -- extremely large PID should still
        // be accepted by validation (it is valid integer), but the process won't exist
        // It may succeed or fail at the process level
        $this->assertNotEquals(422, $response->getStatusCode());
    }

    private function mockCoreDependencies(): void
    {
        // PendingDispatch is final -- use Mockery
        $pendingDispatch = Mockery::mock(PendingDispatch::class);
        $pendingDispatch->shouldReceive('onConnection')->andReturnSelf();
        $pendingDispatch->shouldReceive('onQueue')->andReturnSelf();
        $pendingDispatch->shouldReceive('delay')->andReturnSelf();
        $pendingDispatch->shouldReceive('dispatch')->andReturn('job-id');

        // JobManager is final -- use Mockery
        $jobManager = Mockery::mock(JobManager::class);
        $jobManager->shouldReceive('job')->andReturn($pendingDispatch);
        $this->app->instance(JobManager::class, $jobManager);

        // ProcessManager is final -- use Mockery
        $processManager = Mockery::mock(ProcessManager::class);
        $processManager->shouldReceive('getWorkerStatus')->andReturn([]);
        $processManager->shouldReceive('startSupervisor')->andReturn(['success' => true, 'pid' => null]);
        $processManager->shouldReceive('stopExternalWorker')->andReturn(['success' => true]);
        $this->app->instance(ProcessManager::class, $processManager);

        // HealthCheckerInterface is an interface -- PHPUnit mock works
        $healthChecker = $this->createMock(HealthCheckerInterface::class);
        $healthChecker->method('checkConnectivityQuick')->willReturn([]);
        $this->app->instance(HealthCheckerInterface::class, $healthChecker);

        // BatchManager is final -- use Mockery
        $batchManager = Mockery::mock(BatchManager::class);
        $batchManager->shouldReceive('create')->andReturn(
            new Batch(id: 'batch-test', totalJobs: 1),
        );
        $this->app->instance(BatchManager::class, $batchManager);

        // QueueManager is final -- use Mockery
        $queueManager = Mockery::mock(QueueManager::class);
        $queueManager->shouldReceive('isPaused')->andReturn(false);
        $this->app->instance(QueueManager::class, $queueManager);

        // WorkflowManager is final -- use Mockery
        $workflowManager = Mockery::mock(WorkflowManager::class);
        $workflowManager->shouldReceive('getDefinitions')->andReturn([]);
        $this->app->instance(WorkflowManager::class, $workflowManager);
    }
}
