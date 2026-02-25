<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Batch\CancelBatch;
use App\Actions\Batch\ClearStationBatches;
use App\Actions\Batch\CreateStationBatch;
use App\Actions\Batch\ListStationBatches;
use App\Actions\Batch\RetryBatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class StationBatchController extends Controller
{
    public function store(Request $request, CreateStationBatch $action): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'count' => 'required|integer|min:1|max:1000',
            'queue' => 'nullable|string|max:255',
            'connection' => 'nullable|string|max:255',
            'allowed_failures' => 'nullable|integer|min:0',
            'fail_percentage' => 'nullable|integer|min:0|max:100',
        ]);

        try {
            $result = $action->handle(
                name: $validated['name'] ?? 'Test Batch ' . now()->format('H:i:s'),
                count: (int) $validated['count'],
                queue: $validated['queue'] ?? 'default',
                connection: $validated['connection'] ?? config('queue.default'),
                allowedFailures: (int) ($validated['allowed_failures'] ?? 0),
                failPercentage: (int) ($validated['fail_percentage'] ?? 0),
            );

            return response()->json(['success' => true, ...$result]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $this->safeErrorMessage($e),
            ], 500);
        }
    }

    public function index(Request $request, ListStationBatches $action): JsonResponse
    {
        try {
            return response()->json($action->handle(
                page: max(1, (int) $request->query('page', 1)),
                connection: $request->query('connection'),
                status: $request->query('status'),
            ));
        } catch (Throwable $e) {
            return response()->json([
                'batches' => [],
                'pagination' => ['current_page' => 1, 'last_page' => 1, 'total' => 0, 'per_page' => 10],
                'error' => $this->safeErrorMessage($e),
            ]);
        }
    }

    public function cancel(string $id, CancelBatch $action): JsonResponse
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

    public function retry(string $id, RetryBatch $action): JsonResponse
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

    public function clear(ClearStationBatches $action): JsonResponse
    {
        try {
            $action->handle();

            return response()->json(['success' => true]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'error' => $this->safeErrorMessage($e)], 500);
        }
    }
}
