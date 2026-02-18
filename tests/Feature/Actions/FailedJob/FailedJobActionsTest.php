<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\FailedJob;

use Carbon\CarbonImmutable;
use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Station\Contracts\JobRepositoryInterface;
use Station\Core\Job;
use Station\Core\JobManager;
use Station\DTOs\PaginatedResult;
use Tests\TestCase;

class FailedJobActionsTest extends TestCase
{
    private JobRepositoryInterface $jobRepository;

    private MockInterface $jobManager;

    protected function setUp(): void
    {
        parent::setUp();

        // JobRepositoryInterface is an interface so PHPUnit mocking works
        $this->jobRepository = $this->createMock(JobRepositoryInterface::class);
        $this->app->instance(JobRepositoryInterface::class, $this->jobRepository);

        // JobManager is final so we need Mockery
        $this->jobManager = Mockery::mock(JobManager::class);
        $this->app->instance(JobManager::class, $this->jobManager);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // -- ListFailedJobs tests --

    public function testListFailedJobsReturnsEmptyListWhenNoFailedJobs(): void
    {
        $this->jobRepository->expects($this->once())
            ->method('paginateFailed')
            ->willReturn(PaginatedResult::empty(10));

        $response = $this->getJson('/failed-jobs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'failed_jobs',
                'count',
                'pagination' => ['current_page', 'last_page', 'total', 'per_page'],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('count', 0)
            ->assertJsonPath('failed_jobs', []);
    }

    public function testListFailedJobsReturnsPaginatedResults(): void
    {
        $job = new Job(
            id: 'failed-job-1',
            queue: 'default',
            jobClass: 'App\\Jobs\\TestJob',
            payload: 'serialized payload data',
            status: 'failed',
            connection: 'rabbitmq',
            createdAt: CarbonImmutable::parse('2025-01-01 12:00:00'),
            completedAt: CarbonImmutable::parse('2025-01-01 12:01:00'),
        );

        $result = new PaginatedResult(
            data: [$job],
            total: 1,
            per_page: 10,
            current_page: 1,
            last_page: 1,
            from: 1,
            to: 1,
        );

        $this->jobRepository->expects($this->once())
            ->method('paginateFailed')
            ->willReturn($result);

        $response = $this->getJson('/failed-jobs');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('count', 1)
            ->assertJsonCount(1, 'failed_jobs')
            ->assertJsonPath('failed_jobs.0.id', 'failed-job-1')
            ->assertJsonPath('failed_jobs.0.job_name', 'App\\Jobs\\TestJob')
            ->assertJsonPath('failed_jobs.0.queue', 'default');
    }

    public function testListFailedJobsHandsPageParameter(): void
    {
        $this->jobRepository->expects($this->once())
            ->method('paginateFailed')
            ->with([], 2, 10)
            ->willReturn(PaginatedResult::empty(10));

        $response = $this->getJson('/failed-jobs?page=2');

        $response->assertStatus(200);
    }

    public function testListFailedJobsHandlesRepositoryException(): void
    {
        $this->jobRepository->method('paginateFailed')
            ->willThrowException(new RuntimeException('Database error'));

        $response = $this->getJson('/failed-jobs');

        $response->assertStatus(200)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error', 'Database error')
            ->assertJsonPath('failed_jobs', [])
            ->assertJsonPath('count', 0)
            ->assertJsonStructure([
                'pagination' => ['current_page', 'last_page', 'total', 'per_page'],
            ]);
    }

    public function testListFailedJobsWithNegativePageDefaultsToOne(): void
    {
        $this->jobRepository->expects($this->once())
            ->method('paginateFailed')
            ->with([], 1, 10)
            ->willReturn(PaginatedResult::empty(10));

        $response = $this->getJson('/failed-jobs?page=-5');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function testListFailedJobsTruncatesLongPayload(): void
    {
        $longPayload = str_repeat('x', 500);

        $job = new Job(
            id: 'truncated-job',
            queue: 'default',
            jobClass: 'App\\Jobs\\TestJob',
            payload: $longPayload,
            status: 'failed',
        );

        $result = new PaginatedResult(
            data: [$job],
            total: 1,
            per_page: 10,
            current_page: 1,
            last_page: 1,
            from: 1,
            to: 1,
        );

        $this->jobRepository->expects($this->once())
            ->method('paginateFailed')
            ->willReturn($result);

        $response = $this->getJson('/failed-jobs');

        $response->assertStatus(200);
        $exception = $response->json('failed_jobs.0.exception');
        // Str::limit truncates to 200 characters + '...'
        $this->assertLessThanOrEqual(203, \strlen($exception));
    }

    // -- RetryFailedJob tests --

    public function testRetryFailedJobWithValidUuidReturnsSuccess(): void
    {
        $this->jobManager->shouldReceive('retry')
            ->once()
            ->with('job-uuid-123')
            ->andReturn(true);

        $response = $this->postJson('/failed-jobs/job-uuid-123/retry');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Job queued for retry');
    }

    public function testRetryFailedJobWhenRetryFailsReturnsFailureMessage(): void
    {
        $this->jobManager->shouldReceive('retry')
            ->once()
            ->with('job-uuid-456')
            ->andReturn(false);

        $response = $this->postJson('/failed-jobs/job-uuid-456/retry');

        $response->assertStatus(200)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Unable to retry job');
    }

    public function testRetryFailedJobHandlesExceptionReturns500(): void
    {
        $this->jobManager->shouldReceive('retry')
            ->andThrow(new RuntimeException('Job not found'));

        $response = $this->postJson('/failed-jobs/nonexistent/retry');

        $response->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error', 'Job not found');
    }

    // -- RetryAllFailedJobs tests --

    public function testRetryAllFailedJobsReturnsSuccessWithCount(): void
    {
        $this->jobManager->shouldReceive('retryAllFailed')
            ->once()
            ->andReturn(15);

        $response = $this->postJson('/failed-jobs/retry-all');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('retried', 15)
            ->assertJsonPath('message', 'Retried 15 jobs');
    }

    public function testRetryAllFailedJobsWithZeroJobsReturnsZero(): void
    {
        $this->jobManager->shouldReceive('retryAllFailed')
            ->once()
            ->andReturn(0);

        $response = $this->postJson('/failed-jobs/retry-all');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('retried', 0);
    }

    public function testRetryAllFailedJobsHandlesExceptionReturns500(): void
    {
        $this->jobManager->shouldReceive('retryAllFailed')
            ->andThrow(new RuntimeException('Database locked'));

        $response = $this->postJson('/failed-jobs/retry-all');

        $response->assertStatus(500)
            ->assertJsonPath('success', false);
    }

    // -- DeleteFailedJob tests --

    public function testDeleteFailedJobWithValidUuidReturnsSuccess(): void
    {
        $this->jobRepository->expects($this->once())
            ->method('deleteFailed')
            ->with('delete-uuid-789');

        $response = $this->deleteJson('/failed-jobs/delete-uuid-789');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Failed job deleted');
    }

    public function testDeleteFailedJobHandlesExceptionReturns500(): void
    {
        $this->jobRepository->method('deleteFailed')
            ->willThrowException(new RuntimeException('Record not found'));

        $response = $this->deleteJson('/failed-jobs/nonexistent');

        $response->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error', 'Record not found');
    }

    // -- FlushFailedJobs tests --

    public function testFlushFailedJobsReturnsSuccessWithDeletedCount(): void
    {
        $this->jobRepository->expects($this->once())
            ->method('flushFailed')
            ->willReturn(42);

        $response = $this->deleteJson('/failed-jobs');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('deleted', 42)
            ->assertJsonPath('message', 'Deleted 42 failed jobs');
    }

    public function testFlushFailedJobsWithZeroDeletedReturnsZero(): void
    {
        $this->jobRepository->expects($this->once())
            ->method('flushFailed')
            ->willReturn(0);

        $response = $this->deleteJson('/failed-jobs');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('deleted', 0);
    }

    public function testFlushFailedJobsHandlesExceptionReturns500(): void
    {
        $this->jobRepository->method('flushFailed')
            ->willThrowException(new RuntimeException('Cannot flush'));

        $response = $this->deleteJson('/failed-jobs');

        $response->assertStatus(500)
            ->assertJsonPath('success', false);
    }

    // -- SQL Injection tests --

    public function testRetryFailedJobSqlInjectionInUuidIsHandled(): void
    {
        $this->jobManager->shouldReceive('retry')->andReturn(false);

        $response = $this->postJson("/failed-jobs/'; DROP TABLE failed_jobs; --/retry");

        $response->assertStatus(200);
    }

    public function testDeleteFailedJobSqlInjectionInUuidIsHandled(): void
    {
        $this->jobRepository->method('deleteFailed')
            ->willThrowException(new RuntimeException('Not found'));

        $response = $this->deleteJson("/failed-jobs/' OR 1=1; --");

        $response->assertStatus(500);
    }
}
