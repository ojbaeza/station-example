<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Worker\GetWorkerStatus;
use App\Actions\Worker\LaunchWorker;
use App\Actions\Worker\StartWorker;
use App\Actions\Worker\StopExternalWorker;
use App\Actions\Worker\StopWorker;
use App\Http\Requests\DriverQueueRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Station\Enums\Driver;
use Throwable;

class WorkerController extends Controller
{
    public function launch(Request $request, LaunchWorker $action): JsonResponse
    {
        $validated = $request->validate([
            'connection' => 'required|string|in:' . Driver::validationIn(),
            'queue' => 'nullable|string|max:255',
            'workers' => 'nullable|integer|min:1|max:10',
        ]);

        try {
            return response()->json($action->handle(
                connection: $validated['connection'],
                queue: $validated['queue'] ?? 'default',
                workers: (int) ($validated['workers'] ?? 1),
            ));
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function start(DriverQueueRequest $request, StartWorker $action): JsonResponse
    {
        try {
            return response()->json($action->handle(
                driver: $request->validated('driver'),
                queue: $request->validated('queue') ?? 'default',
            ));
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function stop(Request $request, StopWorker $action): JsonResponse
    {
        $validated = $request->validate([
            'driver' => 'required|string|in:' . Driver::validationIn(),
        ]);

        try {
            return response()->json($action->handle(
                driver: $validated['driver'],
            ));
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function stopExternal(Request $request, StopExternalWorker $action): JsonResponse
    {
        $validated = $request->validate([
            'pid' => 'required|integer|min:1',
        ]);

        try {
            return response()->json($action->handle(
                pid: (int) $validated['pid'],
            ));
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function status(GetWorkerStatus $action): JsonResponse
    {
        try {
            return response()->json($action->handle());
        } catch (Throwable $e) {
            return response()->json([
                'workers' => [],
                'default' => config('queue.default', 'sync'),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
