<?php

declare(strict_types=1);

namespace App\Providers;

use App\Jobs\WorkflowStepJob;
use App\Models\WorkflowDefinition;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Station\Facades\Workflow;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Workflow::define('order-processing')
            ->description('Process an order through validation, payment, and fulfillment')
            ->addStep('validate', WorkflowStepJob::class)
            ->addStep('payment', WorkflowStepJob::class, ['validate'])
            ->addStep('fulfillment', WorkflowStepJob::class, ['payment'])
            ->timeout(300);

        Workflow::define('data-pipeline')
            ->description('Extract, transform, and load data')
            ->addStep('extract', WorkflowStepJob::class)
            ->addParallel('transform', [
                'transform_a' => WorkflowStepJob::class,
                'transform_b' => WorkflowStepJob::class,
            ], ['extract'])
            ->addStep('load', WorkflowStepJob::class, ['transform'])
            ->timeout(600);

        // Load custom definitions from DB (cached to avoid per-request queries)
        $this->loadWorkflowDefinitionsFromDatabase();
    }

    private function loadWorkflowDefinitionsFromDatabase(): void
    {
        try {
            $definitions = Cache::remember('workflow_definitions', 60, static fn() => WorkflowDefinition::all());

            foreach ($definitions as $def) {
                $wf = Workflow::define($def->name)
                    ->description($def->description ?? '')
                    ->timeout($def->timeout)
                    ->source('database');

                foreach ($def->steps as $step) {
                    if (!empty($step['parallel'])) {
                        $jobs = [];
                        foreach ($step['jobs'] ?? [] as $s) {
                            $jobs[$s['name']] = $s['job_class'] ?: WorkflowStepJob::class;
                        }
                        $wf->addParallel($step['name'], $jobs, $step['dependencies'] ?? []);
                    } else {
                        $wf->addStep(
                            $step['name'],
                            $step['job_class'] ?: WorkflowStepJob::class,
                            $step['dependencies'] ?? [],
                        );
                    }
                }
            }
        } catch (Throwable) {
            // Table may not exist yet (before migration)
        }
    }
}
