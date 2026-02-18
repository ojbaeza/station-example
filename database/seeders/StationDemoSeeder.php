<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StationDemoSeeder extends Seeder
{
    private Carbon $now;

    private array $jobClasses = [
        'App\\Jobs\\ProcessPayment',
        'App\\Jobs\\SendNotification',
        'App\\Jobs\\GenerateReport',
        'App\\Jobs\\SyncInventory',
        'App\\Jobs\\ProcessImage',
        'App\\Jobs\\SendEmail',
        'App\\Jobs\\ImportCsvData',
        'App\\Jobs\\RefreshCache',
    ];

    private array $connections = ['rabbitmq', 'redis'];

    private array $queues = ['default', 'high', 'emails', 'reports'];

    private array $tagPool = [
        'user:123', 'user:456', 'user:789', 'user:1024',
        'tenant:acme', 'tenant:globex', 'tenant:initech',
        'priority:high', 'priority:low',
        'order:456', 'order:789', 'order:1122',
        'campaign:spring-sale', 'campaign:welcome',
        'report:monthly', 'report:weekly',
    ];

    private array $exceptions = [
        'ConnectionTimeoutException' => 'Connection to payment gateway timed out after 30s',
        'RateLimitExceededException' => 'Rate limit exceeded: 429 Too Many Requests',
        'InvalidPayloadException' => 'Missing required field "email" in payload',
        'DatabaseDeadlockException' => 'Deadlock found when trying to get lock; try restarting transaction',
        'MemoryLimitException' => 'Allowed memory size of 134217728 bytes exhausted',
        'ApiAuthenticationException' => 'Invalid API key or token expired',
        'FileNotFoundException' => 'File "imports/users_march.csv" not found in storage',
        'QueueConnectionException' => 'Could not connect to RabbitMQ: Connection refused [tcp://rabbitmq:5672]',
    ];

    public function run(): void
    {
        $this->now = Carbon::now();

        $this->command->info('Seeding Station demo data...');

        $this->seedSupervisorsAndWorkers();
        $this->seedQueueStatus();
        $this->seedJobs();
        $this->seedBatches();
        $this->seedMetrics();
        $this->seedWorkflows();
        $this->seedAlerts();
        $this->seedAuditLog();
        $this->seedDriverSnapshots();

        $this->command->info('Station demo data seeded successfully!');
    }

    private function seedJobs(): void
    {
        $this->command->info('  Seeding jobs...');

        $jobs = [];
        $failedJobs = [];
        $jobEvents = [];
        $jobIds = [];

        // Distribution: 30 pending, 10 processing, 140 completed, 20 failed = 200
        $statusCounts = [
            'pending' => 30,
            'processing' => 10,
            'completed' => 140,
            'failed' => 20,
        ];

        foreach ($statusCounts as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $jobId = (string) Str::uuid7();
                $jobIds[] = ['id' => $jobId, 'status' => $status];
                $jobClass = $this->jobClasses[array_rand($this->jobClasses)];
                $connection = $this->connections[array_rand($this->connections)];
                $queue = $this->queues[array_rand($this->queues)];
                $tags = $this->randomTags();
                $createdAt = $this->randomTimestamp(7);
                $processingTime = rand(50, 8000);

                $job = [
                    'id' => $jobId,
                    'queue' => $queue,
                    'connection' => $connection,
                    'job_class' => $jobClass,
                    'payload' => json_encode(['data' => ['id' => rand(1, 9999)]]),
                    'status' => $status,
                    'attempts' => $status === 'pending' ? 0 : ($status === 'failed' ? rand(1, 3) : 1),
                    'max_tries' => 3,
                    'timeout' => 60,
                    'priority' => $this->weightedRandom([0 => 60, 1 => 25, 2 => 15]),
                    'batch_id' => null,
                    'tags' => json_encode($tags),
                    'worker_id' => null,
                    'memory_used' => null,
                    'processing_time' => null,
                    'available_at' => $createdAt,
                    'reserved_at' => null,
                    'started_at' => null,
                    'completed_at' => null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];

                switch ($status) {
                    case 'processing':
                        $startedAt = Carbon::parse($createdAt)->addSeconds(rand(1, 30));
                        $job['reserved_at'] = $startedAt->toDateTimeString();
                        $job['started_at'] = $startedAt->toDateTimeString();
                        $job['worker_id'] = 'worker-' . rand(1, 4);
                        $job['memory_used'] = rand(8_000_000, 64_000_000);
                        $job['updated_at'] = $startedAt->toDateTimeString();

                        break;

                    case 'completed':
                        $startedAt = Carbon::parse($createdAt)->addSeconds(rand(1, 60));
                        $completedAt = $startedAt->copy()->addMilliseconds($processingTime);
                        $job['reserved_at'] = $startedAt->toDateTimeString();
                        $job['started_at'] = $startedAt->toDateTimeString();
                        $job['completed_at'] = $completedAt->toDateTimeString();
                        $job['worker_id'] = 'worker-' . rand(1, 4);
                        $job['memory_used'] = rand(8_000_000, 64_000_000);
                        $job['processing_time'] = $processingTime;
                        $job['updated_at'] = $completedAt->toDateTimeString();

                        break;

                    case 'failed':
                        $startedAt = Carbon::parse($createdAt)->addSeconds(rand(1, 30));
                        $failedAt = $startedAt->copy()->addMilliseconds(rand(100, 5000));
                        $job['reserved_at'] = $startedAt->toDateTimeString();
                        $job['started_at'] = $startedAt->toDateTimeString();
                        $job['worker_id'] = 'worker-' . rand(1, 4);
                        $job['memory_used'] = rand(8_000_000, 128_000_000);
                        $job['processing_time'] = rand(100, 5000);
                        $job['updated_at'] = $failedAt->toDateTimeString();

                        $exceptionClass = array_rand($this->exceptions);
                        $exceptionMessage = $this->exceptions[$exceptionClass];
                        $failedJobs[] = [
                            'id' => (string) Str::uuid7(),
                            'original_id' => $jobId,
                            'queue' => $queue,
                            'connection' => $connection,
                            'job_class' => $jobClass,
                            'payload' => $job['payload'],
                            'exception' => "$exceptionClass: $exceptionMessage\n\n#0 app/Jobs/" . class_basename($jobClass) . ".php(42): handle()\n#1 vendor/laravel/framework/src/Illuminate/Queue/CallQueuedHandler.php(128): call()\n#2 vendor/laravel/framework/src/Illuminate/Queue/Jobs/Job.php(102): fire()",
                            'context' => json_encode(['attempt' => $job['attempts'], 'queue' => $queue]),
                            'batch_id' => null,
                            'tags' => json_encode($tags),
                            'attempts' => $job['attempts'],
                            'failed_at' => $failedAt->toDateTimeString(),
                        ];

                        break;
                }

                $jobs[] = $job;

                // Generate events for non-pending jobs
                if ($status !== 'pending') {
                    $jobEvents[] = [
                        'job_id' => $jobId,
                        'event' => 'dispatched',
                        'worker_id' => null,
                        'attempt' => null,
                        'message' => null,
                        'context' => json_encode(['queue' => $queue, 'connection' => $connection]),
                        'occurred_at' => $createdAt,
                    ];
                    $jobEvents[] = [
                        'job_id' => $jobId,
                        'event' => 'processing',
                        'worker_id' => $job['worker_id'],
                        'attempt' => 1,
                        'message' => null,
                        'context' => null,
                        'occurred_at' => $job['started_at'] ?? $createdAt,
                    ];

                    if ($status === 'completed') {
                        $jobEvents[] = [
                            'job_id' => $jobId,
                            'event' => 'completed',
                            'worker_id' => $job['worker_id'],
                            'attempt' => 1,
                            'message' => null,
                            'context' => json_encode(['processing_time' => $processingTime]),
                            'occurred_at' => $job['completed_at'],
                        ];
                    } elseif ($status === 'failed') {
                        $jobEvents[] = [
                            'job_id' => $jobId,
                            'event' => 'failed',
                            'worker_id' => $job['worker_id'],
                            'attempt' => $job['attempts'],
                            'message' => $this->exceptions[array_rand($this->exceptions)],
                            'context' => null,
                            'occurred_at' => $job['updated_at'],
                        ];
                    }
                }
            }
        }

        // Bulk insert in chunks
        foreach (array_chunk($jobs, 50) as $chunk) {
            DB::table('station_jobs')->insert($chunk);
        }
        foreach (array_chunk($failedJobs, 50) as $chunk) {
            DB::table('station_failed_jobs')->insert($chunk);
        }
        foreach (array_chunk($jobEvents, 100) as $chunk) {
            DB::table('station_job_events')->insert($chunk);
        }

        $this->command->info("    {$this->count($jobs)} jobs, {$this->count($failedJobs)} failed, {$this->count($jobEvents)} events");
    }

    private function seedBatches(): void
    {
        $this->command->info('  Seeding batches...');

        $batches = [
            [
                'name' => 'Import Users Batch',
                'status' => 'completed',
                'total' => 500, 'processed' => 500, 'failed' => 2,
                'daysAgo' => 5, 'durationHours' => 2,
            ],
            [
                'name' => 'Send Campaign Emails',
                'status' => 'processing',
                'total' => 10000, 'processed' => 6543, 'failed' => 12,
                'daysAgo' => 0, 'durationHours' => null,
            ],
            [
                'name' => 'Generate Monthly Reports',
                'status' => 'completed',
                'total' => 48, 'processed' => 48, 'failed' => 0,
                'daysAgo' => 3, 'durationHours' => 1,
            ],
            [
                'name' => 'Process Order Refunds',
                'status' => 'processing',
                'total' => 150, 'processed' => 87, 'failed' => 3,
                'daysAgo' => 0, 'durationHours' => null,
            ],
            [
                'name' => 'Sync Product Catalog',
                'status' => 'completed',
                'total' => 2400, 'processed' => 2400, 'failed' => 0,
                'daysAgo' => 1, 'durationHours' => 4,
            ],
            [
                'name' => 'Nightly Data Cleanup',
                'status' => 'failed',
                'total' => 300, 'processed' => 198, 'failed' => 45,
                'daysAgo' => 2, 'durationHours' => 1,
            ],
            [
                'name' => 'Image Thumbnail Generation',
                'status' => 'cancelled',
                'total' => 1200, 'processed' => 340, 'failed' => 0,
                'daysAgo' => 4, 'durationHours' => null,
            ],
            [
                'name' => 'Welcome Email Sequence',
                'status' => 'pending',
                'total' => 250, 'processed' => 0, 'failed' => 0,
                'daysAgo' => 0, 'durationHours' => null,
            ],
        ];

        $rows = [];
        foreach ($batches as $b) {
            $createdAt = $this->now->copy()->subDays($b['daysAgo'])->subHours(rand(0, 12));
            $startedAt = $b['status'] !== 'pending' ? $createdAt->copy()->addSeconds(rand(1, 30)) : null;
            $finishedAt = null;
            $cancelledAt = null;

            if (\in_array($b['status'], ['completed', 'failed'], true)) {
                $finishedAt = $startedAt?->copy()->addHours($b['durationHours']);
            } elseif ($b['status'] === 'cancelled') {
                $cancelledAt = $startedAt?->copy()->addHours(1);
            }

            $pending = $b['total'] - $b['processed'] - $b['failed'];

            $rows[] = [
                'id' => (string) Str::uuid7(),
                'name' => $b['name'],
                'queue' => $this->queues[array_rand($this->queues)],
                'connection' => $this->connections[array_rand($this->connections)],
                'status' => $b['status'],
                'total_jobs' => $b['total'],
                'pending_jobs' => max(0, $pending),
                'processed_jobs' => $b['processed'],
                'failed_jobs' => $b['failed'],
                'allowed_failures' => $b['status'] === 'failed' ? 10 : 0,
                'failed_job_ids' => json_encode([]),
                'options' => json_encode(null),
                'started_at' => $startedAt?->toDateTimeString(),
                'cancelled_at' => $cancelledAt?->toDateTimeString(),
                'finished_at' => $finishedAt?->toDateTimeString(),
                'created_at' => $createdAt->toDateTimeString(),
                'updated_at' => ($finishedAt ?? $cancelledAt ?? $startedAt ?? $createdAt)->toDateTimeString(),
            ];
        }

        DB::table('station_batches')->insert($rows);
        $this->command->info("    {$this->count($rows)} batches");
    }

    private function seedMetrics(): void
    {
        $this->command->info('  Seeding metrics...');

        $rows = [];
        // 24 hours of data at 5-minute intervals = 288 points per queue
        // Use 4 queues = ~1152 rows total, but let's do last 24h only for density
        $intervalMinutes = 5;
        $totalPoints = (int) (24 * 60 / $intervalMinutes); // 288

        foreach ($this->queues as $queue) {
            $baseProcessed = match ($queue) {
                'high' => 45,
                'default' => 30,
                'emails' => 60,
                'reports' => 8,
                default => 20,
            };

            for ($i = 0; $i < $totalPoints; $i++) {
                $recordedAt = $this->now->copy()->subMinutes(($totalPoints - $i) * $intervalMinutes);
                $hour = (int) $recordedAt->format('H');

                // Realistic throughput curve
                $multiplier = $this->throughputMultiplier($hour);
                $processed = (int) ($baseProcessed * $multiplier * (0.8 + (mt_rand() / mt_getrandmax()) * 0.4));
                $failed = $processed > 0 ? (int) ($processed * (mt_rand(0, 50) / 1000)) : 0;

                $rows[] = [
                    'queue' => $queue,
                    'connection' => $queue === 'emails' ? 'redis' : 'rabbitmq',
                    'jobs_processed' => $processed,
                    'jobs_failed' => $failed,
                    'jobs_pending' => (int) ($processed * (0.1 + (mt_rand() / mt_getrandmax()) * 0.3)),
                    'avg_processing_time' => rand(200, 3000),
                    'avg_wait_time' => rand(50, 800),
                    'peak_memory' => rand(16_000_000, 128_000_000),
                    'active_workers' => $multiplier > 0.3 ? rand(1, 4) : 0,
                    'recorded_at' => $recordedAt->toDateTimeString(),
                ];
            }
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('station_metrics')->insert($chunk);
        }

        $this->command->info("    {$this->count($rows)} metric snapshots");
    }

    private function seedWorkflows(): void
    {
        $this->command->info('  Seeding workflows...');

        $workflows = [
            [
                'defId' => 'order-processing',
                'defName' => 'Order Processing',
                'status' => 'completed',
                'progress' => 100,
                'steps' => ['validate-order', 'charge-payment', 'update-inventory', 'send-confirmation'],
            ],
            [
                'defId' => 'order-processing',
                'defName' => 'Order Processing',
                'status' => 'running',
                'progress' => 50,
                'steps' => ['validate-order', 'charge-payment', 'update-inventory', 'send-confirmation'],
            ],
            [
                'defId' => 'data-pipeline',
                'defName' => 'Data Pipeline',
                'status' => 'completed',
                'progress' => 100,
                'steps' => ['extract-data', 'transform-data', 'validate-schema', 'load-warehouse', 'notify-team'],
            ],
            [
                'defId' => 'data-pipeline',
                'defName' => 'Data Pipeline',
                'status' => 'failed',
                'progress' => 60,
                'steps' => ['extract-data', 'transform-data', 'validate-schema', 'load-warehouse', 'notify-team'],
            ],
            [
                'defId' => 'user-onboarding',
                'defName' => 'User Onboarding',
                'status' => 'running',
                'progress' => 33,
                'steps' => ['create-account', 'send-welcome-email', 'provision-resources'],
            ],
            [
                'defId' => 'user-onboarding',
                'defName' => 'User Onboarding',
                'status' => 'completed',
                'progress' => 100,
                'steps' => ['create-account', 'send-welcome-email', 'provision-resources'],
            ],
            [
                'defId' => 'report-generation',
                'defName' => 'Report Generation',
                'status' => 'completed',
                'progress' => 100,
                'steps' => ['gather-data', 'compute-metrics', 'render-pdf', 'upload-storage'],
            ],
            [
                'defId' => 'report-generation',
                'defName' => 'Report Generation',
                'status' => 'pending',
                'progress' => 0,
                'steps' => ['gather-data', 'compute-metrics', 'render-pdf', 'upload-storage'],
            ],
            [
                'defId' => 'data-pipeline',
                'defName' => 'Data Pipeline',
                'status' => 'completed',
                'progress' => 100,
                'steps' => ['extract-data', 'transform-data', 'validate-schema', 'load-warehouse', 'notify-team'],
            ],
            [
                'defId' => 'order-processing',
                'defName' => 'Order Processing',
                'status' => 'completed',
                'progress' => 100,
                'steps' => ['validate-order', 'charge-payment', 'update-inventory', 'send-confirmation'],
            ],
        ];

        $rows = [];
        foreach ($workflows as $idx => $wf) {
            $createdAt = $this->now->copy()->subDays(rand(0, 6))->subHours(rand(0, 23));
            $startedAt = $wf['status'] !== 'pending' ? $createdAt->copy()->addSeconds(rand(1, 10)) : null;
            $completedAt = $wf['status'] === 'completed' ? $startedAt?->copy()->addMinutes(rand(1, 30)) : null;
            $failedAt = $wf['status'] === 'failed' ? $startedAt?->copy()->addMinutes(rand(1, 15)) : null;

            $stepStatuses = $this->buildStepStatuses($wf['steps'], $wf['status'], $wf['progress']);

            $currentStep = null;
            if ($wf['status'] === 'running') {
                foreach ($stepStatuses as $name => $s) {
                    if ($s['status'] === 'running') {
                        $currentStep = $name;

                        break;
                    }
                }
            }

            $rows[] = [
                'id' => (string) Str::uuid7(),
                'definition_id' => $wf['defId'],
                'definition_name' => $wf['defName'],
                'connection' => $this->connections[array_rand($this->connections)],
                'status' => $wf['status'],
                'current_step' => $currentStep,
                'input' => json_encode(['triggered_by' => 'scheduler', 'run' => $idx + 1]),
                'context' => json_encode([]),
                'results' => $wf['status'] === 'completed' ? json_encode(['success' => true]) : json_encode(null),
                'step_statuses' => json_encode($stepStatuses),
                'definition_steps' => json_encode($wf['steps']),
                'error' => $wf['status'] === 'failed' ? 'Step "validate-schema" failed: Schema validation error - missing required field "customer_id"' : null,
                'progress' => $wf['progress'],
                'started_at' => $startedAt?->toDateTimeString(),
                'completed_at' => ($completedAt ?? $failedAt)?->toDateTimeString(),
                'created_at' => $createdAt->toDateTimeString(),
                'updated_at' => ($completedAt ?? $failedAt ?? $startedAt ?? $createdAt)->toDateTimeString(),
            ];
        }

        DB::table('station_workflows')->insert($rows);
        $this->command->info("    {$this->count($rows)} workflow instances");
    }

    private function seedAlerts(): void
    {
        $this->command->info('  Seeding alerts...');

        // Channels
        $slackId = (string) Str::uuid7();
        $emailId = (string) Str::uuid7();
        $webhookId = (string) Str::uuid7();

        $channels = [
            [
                'id' => $slackId,
                'name' => 'Ops Slack Channel',
                'type' => 'slack',
                'enabled' => true,
                'config' => json_encode(['webhook_url' => 'https://hooks.slack.com/services/T00/B00/xxxx']),
                'created_at' => $this->now->copy()->subDays(30)->toDateTimeString(),
                'updated_at' => $this->now->copy()->subDays(30)->toDateTimeString(),
            ],
            [
                'id' => $emailId,
                'name' => 'On-Call Email',
                'type' => 'email',
                'enabled' => true,
                'config' => json_encode(['address' => 'oncall@example.com']),
                'created_at' => $this->now->copy()->subDays(30)->toDateTimeString(),
                'updated_at' => $this->now->copy()->subDays(30)->toDateTimeString(),
            ],
            [
                'id' => $webhookId,
                'name' => 'PagerDuty Webhook',
                'type' => 'webhook',
                'enabled' => true,
                'config' => json_encode(['url' => 'https://events.pagerduty.com/v2/enqueue', 'method' => 'POST']),
                'created_at' => $this->now->copy()->subDays(30)->toDateTimeString(),
                'updated_at' => $this->now->copy()->subDays(30)->toDateTimeString(),
            ],
        ];

        DB::table('station_alert_channels')->insert($channels);

        // Rules
        $rules = [
            [
                'id' => (string) Str::uuid7(),
                'name' => 'High Failure Rate',
                'type' => 'high_failure_rate',
                'enabled' => true,
                'condition' => json_encode(['threshold' => 10, 'operator' => '>=', 'metric' => 'failure_rate_percent']),
                'window' => 300,
                'channel_ids' => json_encode([$slackId, $emailId]),
                'cooldown' => 600,
                'metadata' => json_encode(['description' => 'Alert when failure rate exceeds 10%']),
                'source' => 'config',
                'last_triggered_at' => $this->now->copy()->subHours(3)->toDateTimeString(),
                'created_at' => $this->now->copy()->subDays(14)->toDateTimeString(),
                'updated_at' => $this->now->copy()->subHours(3)->toDateTimeString(),
            ],
            [
                'id' => (string) Str::uuid7(),
                'name' => 'Queue Backup',
                'type' => 'queue_backup',
                'enabled' => true,
                'condition' => json_encode(['threshold' => 1000, 'operator' => '>=', 'metric' => 'pending_jobs']),
                'window' => 600,
                'channel_ids' => json_encode([$slackId]),
                'cooldown' => 900,
                'metadata' => json_encode(['description' => 'Alert when pending jobs exceed 1000']),
                'source' => 'config',
                'last_triggered_at' => $this->now->copy()->subDays(1)->toDateTimeString(),
                'created_at' => $this->now->copy()->subDays(14)->toDateTimeString(),
                'updated_at' => $this->now->copy()->subDays(1)->toDateTimeString(),
            ],
            [
                'id' => (string) Str::uuid7(),
                'name' => 'Stuck Jobs Detected',
                'type' => 'stuck_jobs',
                'enabled' => true,
                'condition' => json_encode(['threshold' => 5, 'operator' => '>=', 'metric' => 'stuck_count', 'max_processing_seconds' => 3600]),
                'window' => 300,
                'channel_ids' => json_encode([$slackId, $emailId, $webhookId]),
                'cooldown' => 300,
                'metadata' => json_encode(['description' => 'Alert when jobs are stuck processing for over 1h']),
                'source' => 'config',
                'last_triggered_at' => null,
                'created_at' => $this->now->copy()->subDays(14)->toDateTimeString(),
                'updated_at' => $this->now->copy()->subDays(14)->toDateTimeString(),
            ],
            [
                'id' => (string) Str::uuid7(),
                'name' => 'Worker Down',
                'type' => 'worker_down',
                'enabled' => false,
                'condition' => json_encode(['threshold' => 0, 'operator' => '<=', 'metric' => 'active_workers']),
                'window' => 120,
                'channel_ids' => json_encode([$webhookId]),
                'cooldown' => 300,
                'metadata' => json_encode(['description' => 'Alert when no active workers detected']),
                'source' => 'config',
                'last_triggered_at' => $this->now->copy()->subDays(5)->toDateTimeString(),
                'created_at' => $this->now->copy()->subDays(14)->toDateTimeString(),
                'updated_at' => $this->now->copy()->subDays(5)->toDateTimeString(),
            ],
        ];

        DB::table('station_alert_rules')->insert($rules);

        // Alert history
        $ruleMap = collect($rules)->keyBy('name');
        $history = [
            ['rule' => 'High Failure Rate', 'severity' => 'critical', 'resolved' => true, 'hoursAgo' => 3,
                'message' => 'Failure rate at 14.2% on queue "emails" (threshold: 10%)'],
            ['rule' => 'High Failure Rate', 'severity' => 'critical', 'resolved' => true, 'hoursAgo' => 8,
                'message' => 'Failure rate at 22.5% on queue "default" (threshold: 10%)'],
            ['rule' => 'High Failure Rate', 'severity' => 'warning', 'resolved' => true, 'hoursAgo' => 26,
                'message' => 'Failure rate at 11.3% on queue "high" (threshold: 10%)'],
            ['rule' => 'Queue Backup', 'severity' => 'warning', 'resolved' => true, 'hoursAgo' => 24,
                'message' => '1,247 pending jobs on queue "emails" (threshold: 1,000)'],
            ['rule' => 'Queue Backup', 'severity' => 'warning', 'resolved' => false, 'hoursAgo' => 1,
                'message' => '1,089 pending jobs on queue "default" (threshold: 1,000)'],
            ['rule' => 'Queue Backup', 'severity' => 'critical', 'resolved' => true, 'hoursAgo' => 48,
                'message' => '3,412 pending jobs on queue "reports" (threshold: 1,000)'],
            ['rule' => 'Stuck Jobs Detected', 'severity' => 'critical', 'resolved' => true, 'hoursAgo' => 72,
                'message' => '7 stuck jobs detected processing for over 1h'],
            ['rule' => 'Stuck Jobs Detected', 'severity' => 'warning', 'resolved' => true, 'hoursAgo' => 120,
                'message' => '5 stuck jobs detected processing for over 1h'],
            ['rule' => 'Worker Down', 'severity' => 'critical', 'resolved' => true, 'hoursAgo' => 120,
                'message' => 'No active workers detected for 2 minutes'],
            ['rule' => 'Worker Down', 'severity' => 'critical', 'resolved' => true, 'hoursAgo' => 130,
                'message' => 'No active workers detected for 2 minutes'],
            ['rule' => 'High Failure Rate', 'severity' => 'warning', 'resolved' => true, 'hoursAgo' => 50,
                'message' => 'Failure rate at 12.1% on queue "reports" (threshold: 10%)'],
            ['rule' => 'Queue Backup', 'severity' => 'warning', 'resolved' => true, 'hoursAgo' => 36,
                'message' => '1,502 pending jobs on queue "high" (threshold: 1,000)'],
            ['rule' => 'High Failure Rate', 'severity' => 'critical', 'resolved' => false, 'hoursAgo' => 0,
                'message' => 'Failure rate at 18.7% on queue "emails" (threshold: 10%)'],
            ['rule' => 'Stuck Jobs Detected', 'severity' => 'warning', 'resolved' => false, 'hoursAgo' => 0,
                'message' => '6 stuck jobs detected processing for over 1h'],
            ['rule' => 'Queue Backup', 'severity' => 'info', 'resolved' => true, 'hoursAgo' => 60,
                'message' => 'Queue "default" approaching backup threshold (890 pending)'],
        ];

        $historyRows = [];
        foreach ($history as $h) {
            $rule = $ruleMap[$h['rule']];
            $createdAt = $this->now->copy()->subHours($h['hoursAgo']);

            $historyRows[] = [
                'rule_id' => $rule['id'],
                'rule_name' => $h['rule'],
                'type' => $rule['type'],
                'severity' => $h['severity'],
                'message' => $h['message'],
                'context' => json_encode(['evaluated_at' => $createdAt->toIso8601String()]),
                'channels_notified' => $rule['channel_ids'],
                'resolved' => $h['resolved'],
                'resolved_at' => $h['resolved'] ? $createdAt->copy()->addMinutes(rand(5, 120))->toDateTimeString() : null,
                'created_at' => $createdAt->toDateTimeString(),
            ];
        }

        DB::table('station_alert_history')->insert($historyRows);
        $this->command->info("    {$this->count($channels)} channels, {$this->count($rules)} rules, {$this->count($historyRows)} history");
    }

    private function seedAuditLog(): void
    {
        $this->command->info('  Seeding audit log...');

        $events = [
            ['event' => 'worker.start', 'resource_type' => 'worker', 'resource_id' => 'worker-1', 'actor' => 'system', 'hoursAgo' => 48],
            ['event' => 'worker.start', 'resource_type' => 'worker', 'resource_id' => 'worker-2', 'actor' => 'system', 'hoursAgo' => 48],
            ['event' => 'worker.start', 'resource_type' => 'worker', 'resource_id' => 'worker-3', 'actor' => 'system', 'hoursAgo' => 48],
            ['event' => 'worker.start', 'resource_type' => 'worker', 'resource_id' => 'worker-4', 'actor' => 'system', 'hoursAgo' => 24],
            ['event' => 'queue.pause', 'resource_type' => 'queue', 'resource_id' => 'reports', 'actor' => 'user', 'hoursAgo' => 36],
            ['event' => 'queue.resume', 'resource_type' => 'queue', 'resource_id' => 'reports', 'actor' => 'user', 'hoursAgo' => 35],
            ['event' => 'job.retry', 'resource_type' => 'job', 'resource_id' => null, 'actor' => 'user', 'hoursAgo' => 20],
            ['event' => 'job.retry', 'resource_type' => 'job', 'resource_id' => null, 'actor' => 'user', 'hoursAgo' => 18],
            ['event' => 'batch.cancel', 'resource_type' => 'batch', 'resource_id' => null, 'actor' => 'user', 'hoursAgo' => 16],
            ['event' => 'job.retry', 'resource_type' => 'job', 'resource_id' => null, 'actor' => 'api_token', 'hoursAgo' => 12],
            ['event' => 'queue.pause', 'resource_type' => 'queue', 'resource_id' => 'emails', 'actor' => 'user', 'hoursAgo' => 10],
            ['event' => 'queue.resume', 'resource_type' => 'queue', 'resource_id' => 'emails', 'actor' => 'user', 'hoursAgo' => 9],
            ['event' => 'worker.restart', 'resource_type' => 'worker', 'resource_id' => 'worker-2', 'actor' => 'system', 'hoursAgo' => 8],
            ['event' => 'job.retry', 'resource_type' => 'job', 'resource_id' => null, 'actor' => 'user', 'hoursAgo' => 6],
            ['event' => 'job.delete', 'resource_type' => 'job', 'resource_id' => null, 'actor' => 'user', 'hoursAgo' => 5],
            ['event' => 'queue.flush', 'resource_type' => 'queue', 'resource_id' => 'default', 'actor' => 'user', 'hoursAgo' => 4],
            ['event' => 'supervisor.scale', 'resource_type' => 'supervisor', 'resource_id' => 'supervisor-1', 'actor' => 'system', 'hoursAgo' => 3],
            ['event' => 'job.retry', 'resource_type' => 'job', 'resource_id' => null, 'actor' => 'user', 'hoursAgo' => 2],
            ['event' => 'worker.start', 'resource_type' => 'worker', 'resource_id' => 'worker-5', 'actor' => 'system', 'hoursAgo' => 1],
            ['event' => 'job.retry', 'resource_type' => 'job', 'resource_id' => null, 'actor' => 'api_token', 'hoursAgo' => 0],
        ];

        $rows = [];
        foreach ($events as $e) {
            $createdAt = $this->now->copy()->subHours($e['hoursAgo'])->subMinutes(rand(0, 59));
            $rows[] = [
                'event' => $e['event'],
                'actor_type' => $e['actor'],
                'actor_id' => match ($e['actor']) {
                    'user' => '1',
                    'api_token' => 'tok_live_abc123',
                    default => null,
                },
                'actor_name' => match ($e['actor']) {
                    'user' => 'Test User',
                    'api_token' => 'CI/CD Pipeline',
                    default => 'Station Supervisor',
                },
                'resource_type' => $e['resource_type'],
                'resource_id' => $e['resource_id'] ?? (string) Str::uuid7(),
                'context' => json_encode(['source' => 'dashboard']),
                'ip_address' => $e['actor'] === 'system' ? null : '192.168.1.' . rand(1, 254),
                'user_agent' => $e['actor'] === 'system' ? null : 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
                'created_at' => $createdAt->toDateTimeString(),
            ];
        }

        DB::table('station_audit_log')->insert($rows);
        $this->command->info("    {$this->count($rows)} audit entries");
    }

    private function seedSupervisorsAndWorkers(): void
    {
        $this->command->info('  Seeding supervisors and workers...');

        $supervisors = [
            [
                'id' => 'supervisor-1',
                'name' => 'station-supervisor-1',
                'hostname' => 'station-app-01',
                'pid' => 1234,
                'status' => 'running',
                'queues' => json_encode(['default', 'high', 'emails']),
                'options' => json_encode(['max_processes' => 4, 'balance' => 'auto']),
                'processes' => 3,
                'jobs_processed' => 14523,
                'last_heartbeat_at' => $this->now->copy()->subSeconds(5)->toDateTimeString(),
                'created_at' => $this->now->copy()->subDays(2)->toDateTimeString(),
                'updated_at' => $this->now->copy()->subSeconds(5)->toDateTimeString(),
            ],
            [
                'id' => 'supervisor-2',
                'name' => 'station-supervisor-2',
                'hostname' => 'station-app-02',
                'pid' => 5678,
                'status' => 'paused',
                'queues' => json_encode(['reports']),
                'options' => json_encode(['max_processes' => 2, 'balance' => 'simple']),
                'processes' => 1,
                'jobs_processed' => 3210,
                'last_heartbeat_at' => $this->now->copy()->subMinutes(2)->toDateTimeString(),
                'created_at' => $this->now->copy()->subDays(2)->toDateTimeString(),
                'updated_at' => $this->now->copy()->subMinutes(2)->toDateTimeString(),
            ],
        ];

        $workers = [
            [
                'id' => 'worker-1',
                'supervisor_id' => 'supervisor-1',
                'hostname' => 'station-app-01',
                'pid' => 1235,
                'status' => 'processing',
                'queue' => 'default',
                'current_job_id' => null, // will be set if we want
                'memory_usage' => 42_000_000,
                'jobs_processed' => 5124,
                'last_heartbeat_at' => $this->now->copy()->subSeconds(3)->toDateTimeString(),
                'started_at' => $this->now->copy()->subDays(2)->toDateTimeString(),
                'created_at' => $this->now->copy()->subDays(2)->toDateTimeString(),
                'updated_at' => $this->now->copy()->subSeconds(3)->toDateTimeString(),
            ],
            [
                'id' => 'worker-2',
                'supervisor_id' => 'supervisor-1',
                'hostname' => 'station-app-01',
                'pid' => 1236,
                'status' => 'idle',
                'queue' => 'high',
                'current_job_id' => null,
                'memory_usage' => 28_000_000,
                'jobs_processed' => 4891,
                'last_heartbeat_at' => $this->now->copy()->subSeconds(8)->toDateTimeString(),
                'started_at' => $this->now->copy()->subDays(2)->toDateTimeString(),
                'created_at' => $this->now->copy()->subDays(2)->toDateTimeString(),
                'updated_at' => $this->now->copy()->subSeconds(8)->toDateTimeString(),
            ],
            [
                'id' => 'worker-3',
                'supervisor_id' => 'supervisor-1',
                'hostname' => 'station-app-01',
                'pid' => 1237,
                'status' => 'processing',
                'queue' => 'emails',
                'current_job_id' => null,
                'memory_usage' => 55_000_000,
                'jobs_processed' => 4508,
                'last_heartbeat_at' => $this->now->copy()->subSeconds(2)->toDateTimeString(),
                'started_at' => $this->now->copy()->subDays(2)->toDateTimeString(),
                'created_at' => $this->now->copy()->subDays(2)->toDateTimeString(),
                'updated_at' => $this->now->copy()->subSeconds(2)->toDateTimeString(),
            ],
            [
                'id' => 'worker-4',
                'supervisor_id' => 'supervisor-2',
                'hostname' => 'station-app-02',
                'pid' => 5679,
                'status' => 'paused',
                'queue' => 'reports',
                'current_job_id' => null,
                'memory_usage' => 18_000_000,
                'jobs_processed' => 3210,
                'last_heartbeat_at' => $this->now->copy()->subMinutes(2)->toDateTimeString(),
                'started_at' => $this->now->copy()->subDays(1)->toDateTimeString(),
                'created_at' => $this->now->copy()->subDays(1)->toDateTimeString(),
                'updated_at' => $this->now->copy()->subMinutes(2)->toDateTimeString(),
            ],
        ];

        DB::table('station_supervisors')->insert($supervisors);
        DB::table('station_workers')->insert($workers);
        $this->command->info("    {$this->count($supervisors)} supervisors, {$this->count($workers)} workers");
    }

    private function seedDriverSnapshots(): void
    {
        $this->command->info('  Seeding driver snapshots...');

        $rows = [];
        // 6 hours at 5-minute intervals = 72 points per connection
        $totalPoints = 72;

        foreach (['rabbitmq', 'redis'] as $connection) {
            $baseQueueSize = $connection === 'rabbitmq' ? 45 : 25;
            $baseMemory = $connection === 'rabbitmq' ? 256_000_000 : 128_000_000;

            for ($i = 0; $i < $totalPoints; $i++) {
                $recordedAt = $this->now->copy()->subMinutes(($totalPoints - $i) * 5);
                $hour = (int) $recordedAt->format('H');
                $multiplier = $this->throughputMultiplier($hour);

                $rows[] = [
                    'connection' => $connection,
                    'queue_size' => (int) ($baseQueueSize * $multiplier * (0.7 + (mt_rand() / mt_getrandmax()) * 0.6)),
                    'memory_bytes' => (int) ($baseMemory * (0.6 + $multiplier * 0.4) * (0.9 + (mt_rand() / mt_getrandmax()) * 0.2)),
                    'consumers' => rand(2, 6),
                    'ops_rate' => round($multiplier * rand(50, 200) + (mt_rand() / mt_getrandmax()) * 20, 2),
                    'recorded_at' => $recordedAt->toDateTimeString(),
                ];
            }
        }

        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('station_driver_snapshots')->insert($chunk);
        }

        $this->command->info("    {$this->count($rows)} driver snapshots");
    }

    private function seedQueueStatus(): void
    {
        $this->command->info('  Seeding queue status...');

        $queues = [
            ['queue' => 'default', 'paused' => false],
            ['queue' => 'high', 'paused' => false],
            ['queue' => 'emails', 'paused' => false],
            ['queue' => 'reports', 'paused' => true],
        ];

        $rows = [];
        foreach ($queues as $q) {
            $rows[] = [
                'queue' => $q['queue'],
                'paused' => $q['paused'],
                'paused_by' => $q['paused'] ? 'Test User' : null,
                'pause_reason' => $q['paused'] ? 'Maintenance window - report generation paused' : null,
                'paused_at' => $q['paused'] ? $this->now->copy()->subHours(2)->toDateTimeString() : null,
                'resume_at' => $q['paused'] ? $this->now->copy()->addHours(1)->toDateTimeString() : null,
                'created_at' => $this->now->copy()->subDays(7)->toDateTimeString(),
                'updated_at' => $q['paused'] ? $this->now->copy()->subHours(2)->toDateTimeString() : $this->now->copy()->subDays(7)->toDateTimeString(),
            ];
        }

        DB::table('station_queue_status')->insert($rows);
        $this->command->info("    {$this->count($rows)} queue statuses");
    }

    // --- Helpers ---

    private function randomTimestamp(int $maxDaysAgo): string
    {
        return $this->now->copy()
            ->subSeconds(rand(0, $maxDaysAgo * 86400))
            ->toDateTimeString();
    }

    private function randomTags(): array
    {
        $count = rand(1, 3);
        $keys = array_rand($this->tagPool, $count);

        return array_values(array_map(fn($k) => $this->tagPool[$k], (array) $keys));
    }

    /**
     * Returns a multiplier (0.05–1.0) for realistic daily throughput.
     * Low overnight, ramp in the morning, peak at midday, taper evening.
     */
    private function throughputMultiplier(int $hour): float
    {
        return match (true) {
            $hour >= 0 && $hour < 6 => 0.05 + ($hour * 0.02),   // 0.05–0.15
            $hour >= 6 && $hour < 9 => 0.15 + (($hour - 6) * 0.25), // 0.15–0.90
            $hour >= 9 && $hour < 14 => 0.9 + (sin(($hour - 9) * M_PI / 10) * 0.1), // 0.9–1.0
            $hour >= 14 && $hour < 18 => 0.9 - (($hour - 14) * 0.1), // 0.9–0.50
            $hour >= 18 && $hour < 22 => 0.5 - (($hour - 18) * 0.1), // 0.5–0.10
            default => 0.05, // 22–23
        };
    }

    private function weightedRandom(array $weights): int
    {
        $total = array_sum($weights);
        $rand = rand(1, $total);
        $cumulative = 0;

        foreach ($weights as $value => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $value;
            }
        }

        return array_key_first($weights);
    }

    private function buildStepStatuses(array $steps, string $workflowStatus, int $progress): array
    {
        $statuses = [];
        $totalSteps = \count($steps);
        $completedCount = (int) round($totalSteps * ($progress / 100));

        foreach ($steps as $idx => $step) {
            if ($workflowStatus === 'pending') {
                $statuses[$step] = ['status' => 'pending', 'started_at' => null, 'completed_at' => null];
            } elseif ($workflowStatus === 'completed') {
                $statuses[$step] = [
                    'status' => 'completed',
                    'started_at' => $this->now->copy()->subMinutes(($totalSteps - $idx) * 5)->toIso8601String(),
                    'completed_at' => $this->now->copy()->subMinutes(($totalSteps - $idx) * 5 - 3)->toIso8601String(),
                ];
            } elseif ($idx < $completedCount) {
                $statuses[$step] = [
                    'status' => 'completed',
                    'started_at' => $this->now->copy()->subMinutes(($totalSteps - $idx) * 5)->toIso8601String(),
                    'completed_at' => $this->now->copy()->subMinutes(($totalSteps - $idx) * 5 - 3)->toIso8601String(),
                ];
            } elseif ($idx === $completedCount && $workflowStatus === 'running') {
                $statuses[$step] = [
                    'status' => 'running',
                    'started_at' => $this->now->copy()->subMinutes(2)->toIso8601String(),
                    'completed_at' => null,
                ];
            } elseif ($idx === $completedCount && $workflowStatus === 'failed') {
                $statuses[$step] = [
                    'status' => 'failed',
                    'started_at' => $this->now->copy()->subMinutes(2)->toIso8601String(),
                    'completed_at' => null,
                    'error' => 'Schema validation error - missing required field "customer_id"',
                ];
            } else {
                $statuses[$step] = ['status' => 'pending', 'started_at' => null, 'completed_at' => null];
            }
        }

        return $statuses;
    }

    private function count(array $arr): int
    {
        return \count($arr);
    }
}
