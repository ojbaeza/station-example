<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Driver\GetDrivers;
use Illuminate\Http\JsonResponse;
use Throwable;

class DriverController extends Controller
{
    public function index(GetDrivers $action): JsonResponse
    {
        try {
            return response()->json($action->handle());
        } catch (Throwable $e) {
            return response()->json([
                'drivers' => [],
                'default' => config('queue.default', 'sync'),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
