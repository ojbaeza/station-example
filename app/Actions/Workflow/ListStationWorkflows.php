<?php

declare(strict_types=1);

namespace App\Actions\Workflow;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ListStationWorkflows
{
    public function handle(int $page, ?string $status = null, ?string $connection = null): array
    {
        $perPage = 10;

        $query = DB::table('station_workflows')
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($connection) {
            $query->where('context->connection', $connection);
        }

        $total = (clone $query)->count();
        $workflows = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();

        return [
            'workflows' => $workflows->map(static fn($row) => [
                'id' => $row->id,
                'name' => $row->definition_name,
                'status' => $row->status,
                'progress' => $row->progress ?? 0,
                'current_step' => $row->current_step ?? null,
                'step_statuses' => json_decode($row->step_statuses ?? '{}', true),
                'connection' => json_decode($row->context ?? '{}', true)['connection'] ?? null,
                'queue' => json_decode($row->context ?? '{}', true)['queue'] ?? null,
                'error' => isset($row->error) && $row->error ? Str::limit($row->error, 200) : null,
                'started_at' => $row->started_at,
                'completed_at' => $row->completed_at,
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
