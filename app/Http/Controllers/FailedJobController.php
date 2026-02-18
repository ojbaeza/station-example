<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\FailedJob\DeleteFailedJob;
use App\Actions\FailedJob\FlushFailedJobs;
use App\Actions\FailedJob\ListFailedJobs;
use App\Actions\FailedJob\RetryAllFailedJobs;
use App\Actions\FailedJob\RetryFailedJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class FailedJobController extends Controller
{
    public function index(Request $request, ListFailedJobs $action): JsonResponse
    {
        try {
            return response()->json($action->handle(
                page: max(1, (int) $request->query('page', 1)),
            ));
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'failed_jobs' => [],
                'count' => 0,
                'pagination' => ['current_page' => 1, 'last_page' => 1, 'total' => 0, 'per_page' => 10],
            ]);
        }
    }

    public function retry(string $uuid, RetryFailedJob $action): JsonResponse
    {
        try {
            return response()->json($action->handle($uuid));
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function retryAll(RetryAllFailedJobs $action): JsonResponse
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

    public function destroy(string $uuid, DeleteFailedJob $action): JsonResponse
    {
        try {
            $action->handle($uuid);

            return response()->json([
                'success' => true,
                'message' => 'Failed job deleted',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function flush(FlushFailedJobs $action): JsonResponse
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
}
