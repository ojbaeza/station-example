<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Batch\ClearBatches;
use App\Actions\Batch\DispatchJobs;
use App\Actions\Batch\GetBatchStatus;
use App\Actions\Batch\ListBatches;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Station\Enums\Driver;

class BatchController extends Controller
{
    public function dispatch(Request $request, DispatchJobs $action): JsonResponse
    {
        $validated = $request->validate([
            'driver' => 'required|string|in:' . Driver::validationIn() . ',sync',
            'count' => 'required|integer|min:1|max:100000',
            'queue' => 'nullable|string|max:255',
            'delay' => 'nullable|integer|min:0|max:3600',
            'fail_percentage' => 'nullable|integer|min:0|max:100',
        ]);

        $result = $action->handle(
            driver: $validated['driver'],
            count: (int) $validated['count'],
            queue: $validated['queue'] ?? 'default',
            delay: (int) ($validated['delay'] ?? 0),
            failPercentage: (int) ($validated['fail_percentage'] ?? 0),
        );

        return response()->json(['success' => true, ...$result]);
    }

    public function status(string $batchId, GetBatchStatus $action): JsonResponse
    {
        return response()->json($action->handle($batchId));
    }

    public function index(Request $request, ListBatches $action): JsonResponse
    {
        return response()->json($action->handle(
            connection: $request->query('connection'),
            status: $request->query('status'),
        ));
    }

    public function clear(ClearBatches $action): JsonResponse
    {
        return response()->json($action->handle());
    }
}
