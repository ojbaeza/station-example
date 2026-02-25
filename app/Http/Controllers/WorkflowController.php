<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Workflow\CancelWorkflow;
use App\Actions\Workflow\ClearStationWorkflows;
use App\Actions\Workflow\CreateWorkflow;
use App\Actions\Workflow\ListStationWorkflows;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class WorkflowController extends Controller
{
    public function store(Request $request, CreateWorkflow $action): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'steps' => 'required|integer|min:1|max:20',
            'queue' => 'nullable|string|max:255',
            'connection' => 'nullable|string|max:255',
            'parallel_steps' => 'nullable|boolean',
        ]);

        try {
            $result = $action->handle(
                name: $validated['name'] ?? 'Test Workflow ' . now()->format('H:i:s'),
                stepCount: (int) $validated['steps'],
                queue: $validated['queue'] ?? 'default',
                connection: $validated['connection'] ?? config('queue.default'),
                parallel: (bool) ($validated['parallel_steps'] ?? false),
            );

            return response()->json(['success' => true, ...$result]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $this->safeErrorMessage($e),
            ], 500);
        }
    }

    public function index(Request $request, ListStationWorkflows $action): JsonResponse
    {
        try {
            return response()->json($action->handle(
                page: max(1, (int) $request->query('page', 1)),
                status: $request->query('status'),
                connection: $request->query('connection'),
            ));
        } catch (Throwable $e) {
            return response()->json([
                'workflows' => [],
                'pagination' => ['current_page' => 1, 'last_page' => 1, 'total' => 0, 'per_page' => 10],
                'error' => $this->safeErrorMessage($e),
            ]);
        }
    }

    public function cancel(string $id, CancelWorkflow $action): JsonResponse
    {
        try {
            return response()->json($action->handle($id));
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $this->safeErrorMessage($e),
            ], 500);
        }
    }

    public function clear(ClearStationWorkflows $action): JsonResponse
    {
        try {
            $action->handle();

            return response()->json(['success' => true]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'error' => $this->safeErrorMessage($e)], 500);
        }
    }
}
