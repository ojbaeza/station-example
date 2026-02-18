<?php

declare(strict_types=1);

namespace Tests\Feature;

use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Station\Contracts\HealthCheckerInterface;
use Station\Contracts\JobRepositoryInterface;
use Station\Core\Batch;
use Station\Core\BatchManager;
use Station\Core\JobManager;
use Station\Core\PendingDispatch;
use Station\Core\ProcessManager;
use Station\Core\QueueManager;
use Station\DTOs\PaginatedResult;
use Station\Workflows\WorkflowManager;
use Tests\TestCase;

/**
 * Verify all application routes are registered and respond to the correct HTTP methods.
 * This test mocks all Station dependencies to isolate route registration testing
 * from external service availability.
 */
class RoutesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mock all external Station services to prevent real connections
        $this->mockProcessManager();
        $this->mockHealthChecker();
        $this->mockJobRepository();
        $this->mockJobManager();
        $this->mockBatchManager();
        $this->mockQueueManager();
        $this->mockWorkflowManager();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function getRouteProvider(): array
    {
        return [
            'dashboard' => ['/'],
            'drivers' => ['/drivers'],
            'batches_list' => ['/batches'],
            'batch_status' => ['/status/test-batch-id'],
            'station_batches' => ['/api/station/batches'],
            'station_workflows' => ['/api/station/workflows'],
            'workflow_definitions' => ['/api/station/workflow-definitions'],
            'worker_status' => ['/worker/status'],
            'supervisor_status' => ['/supervisor/status'],
            'queue_pause_status' => ['/queue/pause-status'],
            'failed_jobs' => ['/failed-jobs'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function postRouteProvider(): array
    {
        return [
            'dispatch_jobs' => ['/dispatch'],
            'clear_batches' => ['/batches/clear'],
            'create_station_batch' => ['/api/station/batch'],
            'cancel_batch' => ['/api/station/batch/test-id/cancel'],
            'retry_batch' => ['/api/station/batch/test-id/retry'],
            'clear_station_batches' => ['/api/station/batches/clear'],
            'create_workflow' => ['/api/station/workflow'],
            'cancel_workflow' => ['/api/station/workflow/test-id/cancel'],
            'clear_workflows' => ['/api/station/workflows/clear'],
            'create_workflow_definition' => ['/api/station/workflow-definitions'],
            'run_workflow_definition' => ['/api/station/workflow-definitions/run'],
            'worker_launch' => ['/worker/launch'],
            'worker_start' => ['/worker/start'],
            'worker_stop' => ['/worker/stop'],
            'worker_stop_external' => ['/worker/stop-external'],
            'supervisor_start' => ['/supervisor/start'],
            'supervisor_stop' => ['/supervisor/stop'],
            'queue_pause' => ['/queue/pause'],
            'queue_resume' => ['/queue/resume'],
            'retry_failed_job' => ['/failed-jobs/test-uuid/retry'],
            'retry_all_failed' => ['/failed-jobs/retry-all'],
        ];
    }

    // -- GET routes --

    #[DataProvider('getRouteProvider')]
    public function testGetRoutesRespondSuccessfully(string $uri): void
    {
        $response = $this->getJson($uri);

        $this->assertContains(
            $response->getStatusCode(),
            [200, 302],
            "GET {$uri} should return 200 or 302, got {$response->getStatusCode()}",
        );
    }

    // -- POST routes respond (not 404/405) --

    #[DataProvider('postRouteProvider')]
    public function testPostRoutesAreRegistered(string $uri): void
    {
        $response = $this->postJson($uri, []);

        // Should not be 404 (route not found) or 405 (method not allowed)
        $this->assertNotEquals(404, $response->getStatusCode(), "POST {$uri} returned 404");
        $this->assertNotEquals(405, $response->getStatusCode(), "POST {$uri} returned 405");
    }

    // -- PUT route --

    public function testPutWorkflowDefinitionRouteIsRegistered(): void
    {
        $response = $this->putJson('/api/station/workflow-definitions/test-name', []);

        // Route is registered; returns 404 because the 'test-name' definition doesn't exist in DB,
        // or 422 if validation fails. Either way, 405 would mean the route is not registered.
        $this->assertNotEquals(
            405,
            $response->getStatusCode(),
            "PUT /api/station/workflow-definitions/{name} returned 405 (Method Not Allowed)",
        );
    }

    // -- DELETE routes --

    public function testDeleteWorkflowDefinitionRouteIsRegistered(): void
    {
        $response = $this->deleteJson('/api/station/workflow-definitions/test-name');

        $this->assertNotEquals(404, $response->getStatusCode());
    }

    public function testDeleteFailedJobRouteIsRegistered(): void
    {
        $response = $this->deleteJson('/failed-jobs/test-uuid');

        $this->assertNotEquals(404, $response->getStatusCode());
    }

    public function testDeleteFlushFailedJobsRouteIsRegistered(): void
    {
        $response = $this->deleteJson('/failed-jobs');

        $this->assertNotEquals(404, $response->getStatusCode());
    }

    // -- Method not allowed tests --

    public function testGetOnPostOnlyRouteReturns405(): void
    {
        $response = $this->getJson('/dispatch');

        $this->assertEquals(405, $response->getStatusCode());
    }

    public function testPostOnGetOnlyRouteReturns405(): void
    {
        $response = $this->postJson('/drivers');

        $this->assertEquals(405, $response->getStatusCode());
    }

    // -- Helper methods to mock Station services --

    private function mockProcessManager(): void
    {
        $mock = Mockery::mock(ProcessManager::class);
        $mock->shouldReceive('getWorkerStatus')->andReturn([]);
        $mock->shouldReceive('getSupervisorStatus')->andReturn(['running' => false]);
        $mock->shouldReceive('startSupervisor')->andReturn(['success' => true, 'pid' => null]);
        $mock->shouldReceive('stopSupervisor')->andReturn(['success' => true]);
        $mock->shouldReceive('startWorker')->andReturn(['success' => true, 'pid' => null]);
        $mock->shouldReceive('stopWorker')->andReturn(['success' => true, 'stopped' => 0]);
        $mock->shouldReceive('stopExternalWorker')->andReturn(['success' => true]);
        $this->app->instance(ProcessManager::class, $mock);
    }

    private function mockHealthChecker(): void
    {
        $mock = $this->createMock(HealthCheckerInterface::class);
        $mock->method('checkConnectivityQuick')->willReturn([]);
        $this->app->instance(HealthCheckerInterface::class, $mock);
    }

    private function mockJobRepository(): void
    {
        $mock = $this->createMock(JobRepositoryInterface::class);
        $mock->method('paginateFailed')->willReturn(PaginatedResult::empty(10));
        $mock->method('flushFailed')->willReturn(0);
        $this->app->instance(JobRepositoryInterface::class, $mock);
    }

    private function mockJobManager(): void
    {
        $mock = Mockery::mock(JobManager::class);
        $mock->shouldReceive('retry')->andReturn(false);
        $mock->shouldReceive('retryAllFailed')->andReturn(0);
        $mock->shouldReceive('job')->andReturn(Mockery::mock(PendingDispatch::class, static function ($m): void {
            $m->shouldReceive('onConnection')->andReturnSelf();
            $m->shouldReceive('onQueue')->andReturnSelf();
            $m->shouldReceive('delay')->andReturnSelf();
            $m->shouldReceive('dispatch')->andReturn('job-id');
        }));
        $this->app->instance(JobManager::class, $mock);
    }

    private function mockBatchManager(): void
    {
        $mock = Mockery::mock(BatchManager::class);
        $mock->shouldReceive('cancel')->andReturn(false);
        $mock->shouldReceive('retryFailed')->andReturn(0);
        $mock->shouldReceive('create')->andReturn(
            new Batch(id: 'batch-test', totalJobs: 1),
        );
        $this->app->instance(BatchManager::class, $mock);
    }

    private function mockQueueManager(): void
    {
        $mock = Mockery::mock(QueueManager::class);
        $mock->shouldReceive('isPaused')->andReturn(false);
        $this->app->instance(QueueManager::class, $mock);
    }

    private function mockWorkflowManager(): void
    {
        $mock = Mockery::mock(WorkflowManager::class);
        $mock->shouldReceive('getDefinitions')->andReturn([]);
        $mock->shouldReceive('getDefinition')->andReturn(null);
        $mock->shouldReceive('cancel')->andReturn(false);
        $mock->shouldReceive('runAsync')->andReturn(null);
        $this->app->instance(WorkflowManager::class, $mock);
    }
}
