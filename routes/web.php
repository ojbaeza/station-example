<?php

declare(strict_types=1);

use App\Http\Controllers\BatchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\FailedJobController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\StationBatchController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\WorkerController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\WorkflowDefinitionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index']);

// Batch dispatch (cache-based)
Route::post('/dispatch', [BatchController::class, 'dispatch']);
Route::get('/status/{batchId}', [BatchController::class, 'status']);
Route::get('/batches', [BatchController::class, 'index']);
Route::post('/batches/clear', [BatchController::class, 'clear']);

// Drivers
Route::get('/drivers', [DriverController::class, 'index']);

// Station Batches
Route::post('/api/station/batch', [StationBatchController::class, 'store']);
Route::get('/api/station/batches', [StationBatchController::class, 'index']);
Route::post('/api/station/batch/{id}/cancel', [StationBatchController::class, 'cancel']);
Route::post('/api/station/batch/{id}/retry', [StationBatchController::class, 'retry']);
Route::post('/api/station/batches/clear', [StationBatchController::class, 'clear']);

// Workflows
Route::post('/api/station/workflow', [WorkflowController::class, 'store']);
Route::get('/api/station/workflows', [WorkflowController::class, 'index']);
Route::post('/api/station/workflow/{id}/cancel', [WorkflowController::class, 'cancel']);
Route::post('/api/station/workflows/clear', [WorkflowController::class, 'clear']);

// Workflow Definitions
Route::get('/api/station/workflow-definitions', [WorkflowDefinitionController::class, 'index']);
Route::post('/api/station/workflow-definitions', [WorkflowDefinitionController::class, 'store']);
Route::put('/api/station/workflow-definitions/{name}', [WorkflowDefinitionController::class, 'update']);
Route::delete('/api/station/workflow-definitions/{name}', [WorkflowDefinitionController::class, 'destroy']);
Route::post('/api/station/workflow-definitions/run', [WorkflowDefinitionController::class, 'run']);

// Worker control routes
Route::post('/worker/launch', [WorkerController::class, 'launch']);
Route::post('/worker/start', [WorkerController::class, 'start']);
Route::post('/worker/stop', [WorkerController::class, 'stop']);
Route::post('/worker/stop-external', [WorkerController::class, 'stopExternal']);
Route::get('/worker/status', [WorkerController::class, 'status']);

// Supervisor control routes
Route::post('/supervisor/start', [SupervisorController::class, 'start']);
Route::post('/supervisor/stop', [SupervisorController::class, 'stop']);
Route::get('/supervisor/status', [SupervisorController::class, 'status']);

// Queue pause/resume routes
Route::post('/queue/pause', [QueueController::class, 'pause']);
Route::post('/queue/resume', [QueueController::class, 'resume']);
Route::get('/queue/pause-status', [QueueController::class, 'pauseStatus']);

// Failed jobs routes
Route::get('/failed-jobs', [FailedJobController::class, 'index']);
Route::post('/failed-jobs/{uuid}/retry', [FailedJobController::class, 'retry']);
Route::post('/failed-jobs/retry-all', [FailedJobController::class, 'retryAll']);
Route::delete('/failed-jobs/{uuid}', [FailedJobController::class, 'destroy']);
Route::delete('/failed-jobs', [FailedJobController::class, 'flush']);
