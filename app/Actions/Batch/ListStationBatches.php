<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use Illuminate\Support\Facades\DB;

class ListStationBatches
{
    public function handle(int $page, ?string $connection = null, ?string $status = null): array
    {
        $perPage = 10;

        $query = DB::table('station_batches')
            ->orderBy('created_at', 'desc');

        if ($connection) {
            $query->where('connection', $connection);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $total = (clone $query)->count();
        $batches = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();

        return [
            'batches' => $batches->map(static fn($row) => [
                'id' => $row->id,
                'name' => $row->name,
                'status' => $row->status,
                'total_jobs' => $row->total_jobs,
                'pending_jobs' => $row->pending_jobs,
                'completed_jobs' => $row->processed_jobs,
                'failed_jobs' => $row->failed_jobs,
                'progress' => $row->total_jobs > 0
                    ? round(($row->processed_jobs + $row->failed_jobs) / $row->total_jobs * 100, 1)
                    : 0,
                'connection' => $row->connection,
                'queue' => $row->queue,
                'created_at' => $row->created_at,
            ])->values()->toArray(),
            'pagination' => [
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
                'total' => $total,
                'per_page' => $perPage,
            ],
        ];
    }
}
