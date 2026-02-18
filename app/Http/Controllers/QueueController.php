<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Queue\GetQueuePauseStatus;
use App\Actions\Queue\PauseQueue;
use App\Actions\Queue\ResumeQueue;
use App\Http\Requests\DriverQueueRequest;
use Illuminate\Http\JsonResponse;

class QueueController extends Controller
{
    public function pause(DriverQueueRequest $request, PauseQueue $action): JsonResponse
    {
        return response()->json($action->handle(
            driver: $request->validated('driver'),
            queue: $request->validated('queue') ?? 'default',
        ));
    }

    public function resume(DriverQueueRequest $request, ResumeQueue $action): JsonResponse
    {
        return response()->json($action->handle(
            driver: $request->validated('driver'),
            queue: $request->validated('queue') ?? 'default',
        ));
    }

    public function pauseStatus(GetQueuePauseStatus $action): JsonResponse
    {
        return response()->json($action->handle());
    }
}
