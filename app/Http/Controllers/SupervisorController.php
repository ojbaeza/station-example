<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Supervisor\GetSupervisorStatus;
use App\Actions\Supervisor\StartSupervisor;
use App\Actions\Supervisor\StopSupervisor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Station\Enums\Driver;
use Throwable;

class SupervisorController extends Controller
{
    public function start(Request $request, StartSupervisor $action): JsonResponse
    {
        $validated = $request->validate([
            'connection' => 'nullable|string|in:' . Driver::validationIn(),
            'queue' => 'nullable|string|max:255',
            'workers' => 'nullable|integer|min:1|max:10',
        ]);

        try {
            return response()->json($action->handle(
                connection: $validated['connection'] ?? config('queue.default'),
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

    public function stop(StopSupervisor $action): JsonResponse
    {
        try {
            return response()->json($action->handle());
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function status(GetSupervisorStatus $action): JsonResponse
    {
        try {
            return response()->json($action->handle());
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
